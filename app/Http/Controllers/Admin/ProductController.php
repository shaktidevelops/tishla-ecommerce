<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products=Product::with('department')
            ->when($request->filled('q'),function($q)use($request){
                $term='%'.$request->string('q')->toString().'%';
                $q->where(fn($s)=>$s->where('name','like',$term)->orWhere('sku','like',$term));
            })->latest()->paginate(30)->withQueryString();
        return view('admin.products.index',compact('products'));
    }

    public function create()
    {
        return view('admin.products.form',[
            'product'=>new Product(['gst_rate'=>env('TISHLA_GST_RATE',5),'status'=>'draft','min_order_qty'=>1]),
            'departments'=>Department::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data=$this->validated($request);
        $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success','Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form',[
            'product'=>$product,
            'departments'=>Department::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data=$this->validated($request,$product->id);
        $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        $product->update($data);
        return redirect()->route('admin.products.edit',$product)->with('success','Product updated.');
    }

    private function validated(Request $request, ?string $id=null): array
    {
        return $request->validate([
            'sku'=>['required','string','max:80','unique:products,sku,'.$id],
            'slug'=>['nullable','string','max:160','unique:products,slug,'.$id],
            'name'=>['required','string','max:200'],
            'short_description'=>['nullable','string','max:500'],
            'description'=>['nullable','string'],
            'department_id'=>['nullable','uuid'],
            'fabric'=>['nullable','string','max:120'],
            'product_type'=>['nullable','string','max:80'],
            'shoot_type'=>['nullable','string','max:80'],
            'base_price'=>['nullable','numeric','min:0'],
            'gst_rate'=>['required','numeric','min:0','max:100'],
            'status'=>['required','in:draft,active,archived'],
            'featured'=>['nullable','boolean'],
            'min_order_qty'=>['required','integer','min:1'],
            'product_badge'=>['nullable','string','max:80'],
            'care_instructions'=>['nullable','string'],
            'shipping_notes'=>['nullable','string'],
            'fit_notes'=>['nullable','string'],
        ]);
    }
}
