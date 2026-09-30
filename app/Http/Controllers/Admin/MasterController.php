<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\MasterValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    private array $types = ['department' => 'Departments', 'product_type' => 'Product Types', 'shoot_type' => 'Shoot Types'];

    public function index()
    {
        return view('admin.masters.index', [
            'departments' => Department::orderBy('sort_order')->orderBy('name')->get(),
            'productTypes' => MasterValue::where('master_type','product_type')->orderBy('sort_order')->orderBy('name')->get(),
            'shootTypes' => MasterValue::where('master_type','shoot_type')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, $this->types), 404);
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string','max:1000'],
            'sort_order' => ['nullable','integer','min:0'],
        ]);

        if ($type === 'department') {
            $slug = Str::slug($data['name']);
            $request->validate(['name' => ['unique:departments,name']]);
            Department::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => true,
            ]);
        } else {
            $request->validate(['name' => [Rule::unique('master_values','name')->where(fn($q)=>$q->where('master_type',$type))]]);
            MasterValue::create([
                'master_type' => $type,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => true,
            ]);
        }

        return back()->with('success', $this->types[$type].' master value created.');
    }

    public function update(Request $request, string $type, string $id)
    {
        abort_unless(array_key_exists($type, $this->types), 404);
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string','max:1000'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active' => ['nullable','boolean'],
        ]);

        if ($type === 'department') {
            $item = Department::findOrFail($id);
            $item->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool)($data['is_active'] ?? false),
            ]);
        } else {
            $item = MasterValue::where('master_type',$type)->findOrFail($id);
            $item->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool)($data['is_active'] ?? false),
            ]);
        }

        return back()->with('success', $this->types[$type].' master value updated.');
    }

    public function destroy(string $type, string $id)
    {
        abort_unless(array_key_exists($type, $this->types), 404);

        if ($type === 'department') {
            $item = Department::findOrFail($id);
            if ($item->products()->exists()) {
                return back()->with('error', 'This department is still used by products. Deactivate it instead.');
            }
        } else {
            $item = MasterValue::where('master_type',$type)->findOrFail($id);
        }

        $item->delete();
        return back()->with('success', $this->types[$type].' master value removed.');
    }
}