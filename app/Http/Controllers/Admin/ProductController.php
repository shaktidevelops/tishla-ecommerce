<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products=Product::with(['department','variants','images','collections'])
            ->when($request->filled('q'),function($q)use($request){
                $term='%'.$request->string('q')->toString().'%';
                $q->where(fn($s)=>$s->where('name','like',$term)->orWhere('sku','like',$term));
            })
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')->toString()))
            ->when($request->filled('department'),fn($q)=>$q->where('department_id',$request->string('department')->toString()))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.products.index',[
            'products'=>$products,
            'departments'=>Department::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form',[
            'product'=>new Product(['gst_rate'=>env('TISHLA_GST_RATE',5),'status'=>'draft','min_order_qty'=>1]),
            'departments'=>Department::orderBy('sort_order')->get(),
            'collections'=>Collection::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data=$this->validated($request);
        $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        $product=Product::create($data);
        $this->syncCollections($product,$request);
        $this->syncVariants($product,$request);

        return redirect()->route('admin.products.edit',$product)->with('success','Product created and catalogue data saved.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants','collections','images']);
        return view('admin.products.form',[
            'product'=>$product,
            'departments'=>Department::orderBy('sort_order')->get(),
            'collections'=>Collection::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data=$this->validated($request,$product->id);
        $data['slug']=$data['slug'] ?: Str::slug($data['name']);
        $product->update($data);
        $this->syncCollections($product,$request);
        $this->syncVariants($product,$request);

        return back()->with('success','Product, variants and merchandising data updated.');
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

    private function syncCollections(Product $product, Request $request): void
    {
        $ids=collect($request->input('collections',[]))->filter()->values();
        $sync=[];
        foreach($ids as $index=>$id){$sync[$id]=['sort_order'=>$index];}
        $product->collections()->sync($sync);
    }

    private function syncVariants(Product $product, Request $request): void
    {
        $existing=collect($request->input('variant_id',[]))->filter()->values();
        $keep=[];
        $rows=$request->input('variants',[]);
        foreach($rows as $index=>$row){
            if(!is_array($row) || empty($row['name'])) continue;
            $id=$row['id']??null;
            $variant=$id ? ProductVariant::where('product_id',$product->id)->find($id) : new ProductVariant();
            if(!$variant){continue;}
            $variant->product_id=$product->id;
            $variant->sku=$row['sku']??($product->sku.'-'.($index+1));
            $variant->name=$row['name'];
            $variant->size_name=$row['size_name']??null;
            $variant->color_name=$row['color_name']??null;
            $variant->color_hex=$row['color_hex']??null;
            $variant->price=($row['price']??null)!==''?$row['price']:null;
            $variant->compare_at_price=($row['compare_at_price']??null)!==''?$row['compare_at_price']:null;
            $variant->is_active=!empty($row['is_active']);
            $variant->save();
            $keep[]=$variant->id;
        }
        ProductVariant::where('product_id',$product->id)->when(count($keep),fn($q)=>$q->whereNotIn('id',$keep))->delete();
        if(!count($keep) && $product->variants()->count()===0){ /* base product can remain variant-less */ }
    }
}
