<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $locations = DB::table('inventory_locations')->where('is_active', true)->orderBy('name')->get();
        $query = DB::table('inventory_stock as s')
            ->join('inventory_locations as l', 'l.id', '=', 's.location_id')
            ->join('product_variants as v', 'v.id', '=', 's.variant_id')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->select('s.*', 'l.name as location_name', 'l.code as location_code', 'v.name as variant_name', 'v.sku as variant_sku', 'p.name as product_name', 'p.slug as product_slug')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q')->toString() . '%';
                $q->where(function ($s) use ($term) {
                    $s->where('p.name', 'like', $term)->orWhere('v.sku', 'like', $term)->orWhere('v.name', 'like', $term);
                });
            })
            ->when($request->filled('location'), fn ($q) => $q->where('s.location_id', $request->string('location')->toString()))
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereRaw('(s.on_hand - s.reserved) <= s.reorder_level'))
            ->orderBy('p.name')->orderBy('v.name');

        $stock = $query->paginate(35)->withQueryString();
        $stats = [
            'skus' => DB::table('inventory_stock')->count(),
            'units' => (int) DB::table('inventory_stock')->sum('on_hand'),
            'reserved' => (int) DB::table('inventory_stock')->sum('reserved'),
            'low' => (int) DB::table('inventory_stock')->whereRaw('(on_hand - reserved) <= reorder_level')->count(),
        ];

        return view('admin.inventory.index', compact('stock', 'locations', 'stats'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'uuid', 'exists:product_variants,id'],
            'location_id' => ['required', 'uuid', 'exists:inventory_locations,id'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            $stock = DB::table('inventory_stock')->where(['location_id' => $data['location_id'], 'variant_id' => $data['variant_id']])->lockForUpdate()->first();
            if (!$stock) {
                if ((int)$data['quantity'] < 0) abort(422, 'Cannot reduce stock for a variant/location that has no stock record yet.');
                $id = (string) Str::uuid();
                DB::table('inventory_stock')->insert([
                    'id' => $id,
                    'location_id' => $data['location_id'],
                    'variant_id' => $data['variant_id'],
                    'on_hand' => max(0, (int) $data['quantity']),
                    'reserved' => 0,
                    'reorder_level' => (int) ($data['reorder_level'] ?? 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $delta = (int) $data['quantity'];
            } else {
                $newOnHand = (int) $stock->on_hand + (int) $data['quantity'];
                if ($newOnHand < (int) $stock->reserved) {
                    abort(422, 'Stock cannot fall below the quantity already reserved.');
                }
                DB::table('inventory_stock')->where('id', $stock->id)->update([
                    'on_hand' => $newOnHand,
                    'reorder_level' => $data['reorder_level'] ?? $stock->reorder_level,
                    'updated_at' => now(),
                ]);
                $delta = (int) $data['quantity'];
            }

            DB::table('inventory_movements')->insert([
                'id' => (string) Str::uuid(),
                'location_id' => $data['location_id'],
                'variant_id' => $data['variant_id'],
                'movement_type' => $delta > 0 ? 'receive' : 'adjustment',
                'quantity' => $delta,
                'reference_type' => 'manual',
                'reference_id' => null,
                'reason' => $data['reason'] ?? 'Manual inventory adjustment',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Inventory updated and movement recorded.');
    }
}
