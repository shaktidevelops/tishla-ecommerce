<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Department;
use App\Models\MasterValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['department','variants','images','collections'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $q->where(fn ($s) => $s->where('name','like',$term)->orWhere('sku','like',$term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status',$request->string('status')->toString()))
            ->when($request->filled('department'), fn ($q) => $q->where('department_id',$request->string('department')->toString()))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['gst_rate'=>env('TISHLA_GST_RATE',5),'status'=>'draft','min_order_qty'=>1]),
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
            'productTypes' => MasterValue::where('master_type','product_type')->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(),
            'shootTypes' => MasterValue::where('master_type','shoot_type')->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(),
            'collections' => Collection::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $product = Product::create($data);
        $this->syncCollections($product,$request);
        $this->syncVariants($product,$request);

        return redirect()->route('admin.products.edit',$product)->with('success','Product created and catalogue data saved.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants','collections','images']);

        return view('admin.products.form', [
            'product' => $product,
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
            'productTypes' => MasterValue::where('master_type','product_type')->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(),
            'shootTypes' => MasterValue::where('master_type','shoot_type')->where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(),
            'collections' => Collection::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request,$product->id);
        $data['featured']=$request->boolean('featured');
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $product->update($data);
        $this->syncCollections($product,$request);
        $this->syncVariants($product,$request);

        return back()->with('success','Product, variants and merchandising data updated.');
    }

    public function export(Request $request)
    {
        $rows = Product::with(['department','variants'])->latest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out, $this->csvHeaders());
            foreach ($rows as $product) {
                $variant = $product->variants->first();
                fputcsv($out, [
                    $product->sku,$product->slug,$product->name,$product->department?->name,
                    $product->product_type,$product->fabric,$product->shoot_type,$product->base_price,
                    $product->gst_rate,$product->status,$product->featured ? 1 : 0,$product->min_order_qty,
                    $product->product_badge,$product->fit_notes,$variant?->sku,$variant?->name,$variant?->size_name,
                    $variant?->color_name,$variant?->color_hex,$variant?->price,$variant?->compare_at_price,
                    $variant?->is_active ? 1 : 0,
                ]);
            }
            fclose($out);
        }, 'tishla-products-'.now()->format('Ymd-His').'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output','w');
            fputcsv($out,$this->csvHeaders());
            fputcsv($out,[
                'TS-NEW-001','sample-product','Sample Tishla Product','Sarees','Saree','Organza','Model Shoot',
                '2499','5','draft','0','1','New','Add fit notes here','TS-NEW-001-DEFAULT','Default',
                'Free Size','Wine','#6E102B','2499','','1'
            ]);
            fclose($out);
        }, 'tishla-products-template.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        return $this->processCsv($request,'create');
    }

    public function bulkUpdate(Request $request)
    {
        return $this->processCsv($request,'update');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate(['ids'=>['required','array','min:1'],'ids.*'=>['uuid','exists:products,id']]);

        DB::transaction(function () use ($data) {
            $productIds = $data['ids'];
            $variantIds = ProductVariant::whereIn('product_id',$productIds)->pluck('id')->all();

            if ($variantIds) {
                DB::table('inventory_movements')->whereIn('variant_id',$variantIds)->delete();
                DB::table('inventory_stock')->whereIn('variant_id',$variantIds)->delete();
                ProductVariant::whereIn('id',$variantIds)->delete();
            }

            DB::table('collection_products')->whereIn('product_id',$productIds)->delete();
            DB::table('product_images')->whereIn('product_id',$productIds)->delete();
            Product::whereIn('id',$productIds)->delete();
        });

        return back()->with('success',count($data['ids']).' product(s) deleted with catalogue relationships cleaned.');
    }

    private function processCsv(Request $request, string $mode)
    {
        $data = $request->validate([
            'file'=>['required','file','mimes:csv,txt','max:10240'],
        ]);

        $handle = fopen($data['file']->getRealPath(),'r');
        if (!$handle) return back()->with('error','The uploaded CSV could not be read.');

        $headers = fgetcsv($handle);
        if (!$headers) return back()->with('error','The CSV is empty.');
        $headers = array_map(fn($v)=>Str::of((string)$v)->trim()->lower()->replace(' ','_')->toString(),$headers);
        $required = ['sku','name'];
        if (array_diff($required, $headers)) {
            fclose($handle);
            return back()->with('error','CSV must contain these columns: '.implode(', ',$required));
        }

        $success=0; $errors=[]; $seen=[];
        while (($values=fgetcsv($handle)) !== false) {
            if (count(array_filter($values,fn($v)=>trim((string)$v)!=='')) === 0) continue;
            if (count($values) > count($headers)) { $errors[]='Row '.(count($errors)+2).' — too many CSV columns.'; continue; }
            $values = array_pad($values,count($headers),null);
            $row = array_combine($headers,$values);
            $sku = trim((string)($row['sku'] ?? ''));
            try {
                if (!$sku) throw new \RuntimeException('SKU is required.');
                if (isset($seen[$sku])) throw new \RuntimeException('Duplicate SKU in this file.');
                $seen[$sku]=true;

                DB::transaction(function () use ($mode,$row,$sku,&$success) {
                    $existing = Product::where('sku',$sku)->first();
                    if ($mode === 'create' && $existing) throw new \RuntimeException('SKU already exists.');
                    if ($mode === 'update' && !$existing) throw new \RuntimeException('SKU does not exist.');

                    $product = $existing ?: new Product();
                    $name = trim((string)($row['name'] ?? ''));
                    if ($mode === 'create' && $name === '') throw new \RuntimeException('Product name is required.');
                    if ($name !== '') $product->name = $name;
                    if ($mode === 'create' && !$product->name) throw new \RuntimeException('Product name is required.');

                    if ($mode === 'create' || array_key_exists('slug',$row)) {
                        $slug = trim((string)($row['slug'] ?? ''));
                        if ($slug !== '') $product->slug = $slug;
                        elseif (!$product->slug) $product->slug = Str::slug($product->name);
                    }
                    $product->sku = $sku;

                    if (array_key_exists('department',$row)) $product->department_id = $this->findDepartment($row['department'] ?? '')?->id;
                    if (array_key_exists('product_type',$row) && trim((string)$row['product_type'])!=='') $product->product_type = $this->findMasterName('product_type',$row['product_type']);
                    if (array_key_exists('fabric',$row) && trim((string)$row['fabric'])!=='') $product->fabric = $this->nullableString($row['fabric']);
                    if (array_key_exists('shoot_type',$row) && trim((string)$row['shoot_type'])!=='') $product->shoot_type = $this->findMasterName('shoot_type',$row['shoot_type']);
                    if (array_key_exists('base_price',$row) && trim((string)$row['base_price'])!=='') $product->base_price = $this->nullableNumber($row['base_price']);
                    if (array_key_exists('gst_rate',$row) && trim((string)$row['gst_rate'])!=='') $product->gst_rate = $this->nullableNumber($row['gst_rate']);
                    elseif (!$product->gst_rate) $product->gst_rate = (float)env('TISHLA_GST_RATE',5);
                    if (array_key_exists('status',$row) && trim((string)$row['status'])!=='') {
                        $status=trim((string)$row['status']);
                        if (!in_array($status,['draft','active','archived'],true)) throw new \RuntimeException('Invalid status.');
                        $product->status=$status;
                    } elseif (!$product->status) $product->status='draft';
                    if (array_key_exists('featured',$row) && trim((string)$row['featured'])!=='') $product->featured=in_array(strtolower(trim((string)$row['featured'])),['1','true','yes'],true);
                    if (array_key_exists('min_order_qty',$row) && trim((string)$row['min_order_qty'])!=='') $product->min_order_qty=max(1,(int)$row['min_order_qty']);
                    elseif (!$product->min_order_qty) $product->min_order_qty=1;
                    if (array_key_exists('product_badge',$row) && trim((string)$row['product_badge'])!=='') $product->product_badge=$this->nullableString($row['product_badge']);
                    if (array_key_exists('fit_notes',$row) && trim((string)$row['fit_notes'])!=='') $product->fit_notes=$this->nullableString($row['fit_notes']);


                    $variantSku = trim((string)($row['variant_sku'] ?? ''));
                    $variantName = trim((string)($row['variant_name'] ?? ''));
                    if ($variantSku && $variantName) {
                        $variant = ProductVariant::where('sku',$variantSku)->first();
                        if ($variant && $variant->product_id !== $product->id) throw new \RuntimeException('Variant SKU belongs to another product.');
                        $variant = $variant ?: new ProductVariant();
                        $variant->product_id=$product->id;
                        $variant->sku=$variantSku;
                        $variant->name=$variantName;
                        $variant->size_name=$this->nullableString($row['size_name'] ?? null);
                        $variant->color_name=$this->nullableString($row['color_name'] ?? null);
                        $variant->color_hex=$this->nullableString($row['color_hex'] ?? null);
                        $variant->price=$this->nullableNumber($row['variant_price'] ?? null);
                        $variant->compare_at_price=$this->nullableNumber($row['compare_at_price'] ?? null);
                        $variant->is_active=in_array(strtolower(trim((string)($row['variant_active'] ?? '1'))),['1','true','yes'],true);
                        $variant->save();
                    }

                    $success++;
                });
            } catch (\Throwable $e) {
                $errors[] = 'Row '.($success + count($errors) + 2).' · '.$sku.' — '.$e->getMessage();
            }
        }
        fclose($handle);

        $message = ($mode==='create'?'Bulk upload':'Bulk update').' completed: '.$success.' row(s) processed.';
        if ($errors) $message .= ' '.count($errors).' row(s) failed.';
        return back()->with($errors ? 'error' : 'success',$message)->with('import_errors',$errors);
    }

    private function csvHeaders(): array
    {
        return ['sku','slug','name','department','product_type','fabric','shoot_type','base_price','gst_rate','status','featured','min_order_qty','product_badge','fit_notes','variant_sku','variant_name','size_name','color_name','color_hex','variant_price','compare_at_price','variant_active'];
    }

    private function findDepartment(?string $value): ?Department
    {
        $value=trim((string)$value);
        if ($value==='') return null;
        return Department::where('is_active',true)->where(fn($q)=>$q->where('name',$value)->orWhere('slug',$value))->firstOrFail();
    }

    private function findMasterName(string $type, ?string $value): ?string
    {
        $value=trim((string)$value);
        if ($value==='') return null;
        return MasterValue::where('master_type',$type)->where('is_active',true)
            ->where(fn($q)=>$q->where('name',$value)->orWhere('slug',$value))->value('name')
            ?? throw new \RuntimeException(ucwords(str_replace('_',' ',$type)).' master value not found.');
    }

    private function nullableString($value): ?string
    {
        $value=trim((string)$value);
        return $value==='' ? null : $value;
    }

    private function nullableNumber($value): ?float
    {
        return trim((string)$value)==='' ? null : (float)$value;
    }

    private function validated(Request $request, ?string $id=null): array
    {
        return $request->validate([
            'sku'=>['required','string','max:80','unique:products,sku,'.$id],
            'slug'=>['nullable','string','max:160','unique:products,slug,'.$id],
            'name'=>['required','string','max:200'],
            'short_description'=>['nullable','string','max:500'],
            'description'=>['nullable','string'],
            'department_id'=>['nullable','uuid','exists:departments,id'],
            'fabric'=>['nullable','string','max:120'],
            'product_type'=>['nullable','string','max:80',function($attribute,$value,$fail){ if($value!==null && $value!=='' && !MasterValue::where('master_type','product_type')->where('is_active',true)->where('name',$value)->exists()) $fail('Select a valid Product Type master value.'); }],
            'shoot_type'=>['nullable','string','max:80',function($attribute,$value,$fail){ if($value!==null && $value!=='' && !MasterValue::where('master_type','shoot_type')->where('is_active',true)->where('name',$value)->exists()) $fail('Select a valid Shoot Type master value.'); }],
            'base_price'=>['nullable','numeric','min:0'],
            'gst_rate'=>['required','numeric','min:0','max:100'],
            'status'=>['required','in:draft,active,archived'],
            'featured'=>['nullable','boolean'],
            'min_order_qty'=>['required','integer','min:1'],
            'product_badge'=>['nullable','string','max:80'],
            'fit_notes'=>['nullable','string'],
        ]);
    }

    private function syncCollections(Product $product, Request $request): void
    {
        $ids=collect($request->input('collections',[]))->filter()->values();
        $sync=[];
        foreach($ids as $index=>$id) $sync[$id]=['sort_order'=>$index];
        $product->collections()->sync($sync);
    }

    private function syncVariants(Product $product, Request $request): void
    {
        $keep=[];
        foreach($request->input('variants',[]) as $index=>$row){
            if(!is_array($row) || empty($row['name'])) continue;
            $id=$row['id']??null;
            $variant=$id ? ProductVariant::where('product_id',$product->id)->find($id) : new ProductVariant();
            if(!$variant) continue;
            $variant->product_id=$product->id;
            $variant->sku=trim((string)($row['sku']??'')) ?: $product->sku.'-'.($index+1);
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
        ProductVariant::where('product_id',$product->id)
            ->when(count($keep),fn($q)=>$q->whereNotIn('id',$keep))
            ->delete();
    }
}
