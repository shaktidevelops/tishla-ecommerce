<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return view('storefront.cart', ['cart' => $request->session()->get('cart', [])]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'variant_id' => ['nullable','uuid'],
            'quantity' => ['required','integer','min:1','max:20'],
        ]);

        abort_unless($product->status === 'active', 404);

        $variant = $product->variants()->where('is_active',true)
            ->when($data['variant_id'] ?? null, fn ($query,$id) => $query->where('id',$id))
            ->first();

        if ($product->variants()->exists() && ! $variant) {
            return back()->with('error','Please choose a valid variant.');
        }

        $variantId = $variant?->id ? (string)$variant->id : 'base';
        $key = $product->id.'-'.$variantId;
        $cart = $request->session()->get('cart', []);

        $cart[$key] ??= [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'sku' => $variant?->sku ?? $product->sku,
            'name' => $product->name,
            'variant_name' => $variant?->name,
            'price' => (float)($variant?->price ?? $product->base_price),
            'quantity' => 0,
            'image_url' => $product->images()->first()?->public_url ?? asset('favicon.svg'),
        ];

        $cart[$key]['quantity'] += (int)$data['quantity'];
        $request->session()->put('cart',$cart);

        return redirect()->route('cart')->with('success','Added to bag.');
    }

    public function update(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        foreach ($request->input('quantity', []) as $key=>$qty) {
            if (isset($cart[$key])) $cart[$key]['quantity'] = max(1,min(20,(int)$qty));
        }
        $request->session()->put('cart',$cart);
        return back()->with('success','Bag updated.');
    }

    public function remove(Request $request, string $key)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$key]);
        $request->session()->put('cart',$cart);
        return back()->with('success','Item removed.');
    }
}
