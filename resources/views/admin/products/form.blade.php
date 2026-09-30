@extends('layouts.admin')
@section('content')
<div class="admin-page-head">
  <div><span class="eyebrow">CATALOGUE STUDIO</span><h1>{{ $product->exists?'Edit product':'New product' }}</h1><p>Create or maintain the product record, variants, pricing and merchandising data.</p></div>
  <div class="head-actions"><a class="btn btn-light" href="{{ route('admin.products.index') }}">BACK TO CATALOGUE</a>@if($product->exists)<a class="btn btn-dark" href="{{ route('product',$product->slug) }}" target="_blank" rel="noopener">VIEW STOREFRONT ↗</a>@endif</div>
</div>

@if($errors->any())<div class="admin-card error-box">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<section class="studio-layout">
<form method="post" action="{{ $product->exists?route('admin.products.update',$product):route('admin.products.store') }}" class="studio-main">
@csrf @if($product->exists)@method('PUT')@endif

<section class="admin-card studio-card">
  <div class="studio-card-head"><div><span class="eyebrow">01 · PRODUCT</span><h2>Product information</h2></div><span class="studio-counter">CORE RECORD</span></div>
  <div class="form-grid three">
    <label class="field"><span>Product name</span><input name="name" value="{{ old('name',$product->name) }}" placeholder="e.g. Gulbahar Organza Saree" required></label>
    <label class="field"><span>SKU</span><input name="sku" value="{{ old('sku',$product->sku) }}" placeholder="TSH-..." required></label>
    <label class="field"><span>Slug</span><input name="slug" value="{{ old('slug',$product->slug) }}" placeholder="auto-generated if blank"></label>
    <label class="field"><span>Department</span><select name="department_id"><option value="">Select department</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id',$product->department_id)==$department->id)>{{ $department->name }}</option>@endforeach</select></label>
    <label class="field"><span>Product type</span><select name="product_type"><option value="">Select product type</option>@foreach($productTypes as $type)<option value="{{ $type->name }}" @selected(old('product_type',$product->product_type)===$type->name)>{{ $type->name }}</option>@endforeach</select></label>
    <label class="field"><span>Fabric</span><input name="fabric" value="{{ old('fabric',$product->fabric) }}" placeholder="Organza / Silk / Georgette"></label>
    <label class="field"><span>Shoot type</span><select name="shoot_type"><option value="">Select shoot type</option>@foreach($shootTypes as $type)<option value="{{ $type->name }}" @selected(old('shoot_type',$product->shoot_type)===$type->name)>{{ $type->name }}</option>@endforeach</select></label>
    <label class="field"><span>Minimum order qty</span><input type="number" name="min_order_qty" value="{{ old('min_order_qty',$product->min_order_qty?:1) }}" min="1" required></label>
    <label class="field"><span>Status</span><select name="status">@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(old('status',$product->status)===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
  </div>
  <div class="master-inline-note"><span>MASTER DATA</span><p>Department, product type and shoot type are controlled centrally. Update the available values from <a href="{{ route('admin.masters.index') }}">Catalogue Masters</a>.</p></div>
</section>

<section class="admin-card studio-card">
  <div class="studio-card-head"><div><span class="eyebrow">02 · PRICING</span><h2>Retail pricing</h2></div></div>
  <div class="form-grid three">
    <label class="field"><span>Retail price · INR</span><input type="number" step="0.01" name="base_price" value="{{ old('base_price',$product->base_price) }}" placeholder="0.00"></label>
    <label class="field"><span>GST rate · %</span><input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate',$product->gst_rate) }}" required></label>
    <label class="field"><span>Product badge</span><input name="product_badge" value="{{ old('product_badge',$product->product_badge) }}" placeholder="New / Bestseller / Exclusive"></label>
  </div>
  <div class="price-preview"><span>DISPLAY PRICE</span><strong>₹{{ number_format((float)old('base_price',$product->base_price),0) }}</strong><small>Customer-facing product price before future promotion rules.</small></div>
</section>

<section class="admin-card studio-card">
  <div class="studio-card-head"><div><span class="eyebrow">03 · VARIANTS</span><h2>Purchasable options</h2></div><button type="button" class="btn btn-light" data-add-variant>+ ADD VARIANT</button></div>
  <div class="variant-editor" data-variant-editor>
    @php $variantRows=old('variants', $product->variants->map(fn($v)=>['id'=>$v->id,'sku'=>$v->sku,'name'=>$v->name,'size_name'=>$v->size_name,'color_name'=>$v->color_name,'color_hex'=>$v->color_hex,'price'=>$v->price,'compare_at_price'=>$v->compare_at_price,'is_active'=>$v->is_active])->values()->all()); @endphp
    @forelse($variantRows as $i=>$variant)
      <div class="variant-row" data-variant-row>
        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant['id']??'' }}">
        <label class="field compact"><span>Variant</span><input name="variants[{{ $i }}][name]" value="{{ $variant['name']??'' }}" placeholder="M · Wine"></label>
        <label class="field compact"><span>Size</span><input name="variants[{{ $i }}][size_name]" value="{{ $variant['size_name']??'' }}" placeholder="M"></label>
        <label class="field compact"><span>Colour</span><input name="variants[{{ $i }}][color_name]" value="{{ $variant['color_name']??'' }}" placeholder="Wine"></label>
        <label class="field compact"><span>HEX</span><input name="variants[{{ $i }}][color_hex]" value="{{ $variant['color_hex']??'' }}" placeholder="#6E102B"></label>
        <label class="field compact"><span>SKU</span><input name="variants[{{ $i }}][sku]" value="{{ $variant['sku']??'' }}" placeholder="auto-SKU"></label>
        <label class="field compact"><span>Price</span><input type="number" step="0.01" name="variants[{{ $i }}][price]" value="{{ $variant['price']??'' }}" placeholder="Optional"></label>
        <label class="field compact"><span>Compare at</span><input type="number" step="0.01" name="variants[{{ $i }}][compare_at_price]" value="{{ $variant['compare_at_price']??'' }}" placeholder="Optional"></label>
        <label class="toggle compact"><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked(!array_key_exists('is_active',$variant) || $variant['is_active'])> Active</label>
        <button type="button" class="variant-remove" data-remove-variant aria-label="Remove variant">×</button>
      </div>
    @empty
      <div class="variant-empty" data-variant-empty><span>＋</span><strong>No variants yet.</strong><small>Add sizes, colours or other purchasable combinations.</small></div>
    @endforelse
  </div>
</section>

<section class="admin-card studio-card">
  <div class="studio-card-head"><div><span class="eyebrow">04 · COLLECTIONS & VISIBILITY</span><h2>Catalogue placement</h2></div></div>
  <div class="form-grid two">
    <div class="collection-picker"><span class="field-title">COLLECTIONS</span><div class="check-grid">@foreach($collections as $collection)<label class="check-card"><input type="checkbox" name="collections[]" value="{{ $collection->id }}" @checked($product->collections->contains($collection->id) || in_array($collection->id,old('collections',[])))><span><b>{{ $collection->name }}</b><small>{{ $collection->is_featured?'Featured collection':'Collection' }}</small></span></label>@endforeach</div></div>
    <div><span class="field-title">STORE VISIBILITY</span><label class="toggle big"><input type="checkbox" name="featured" value="1" @checked(old('featured',$product->featured))><span><b>Featured product</b><small>Allow this piece to appear in premium featured placements.</small></span></label></div>
  </div>
</section>

<section class="admin-card studio-card">
  <div class="studio-card-head"><div><span class="eyebrow">05 · PRODUCT CONTENT</span><h2>Customer-facing content</h2></div></div>
  <label class="field"><span>Short description</span><textarea name="short_description" rows="3" placeholder="The one-paragraph product hook…">{{ old('short_description',$product->short_description) }}</textarea></label>
  <label class="field"><span>Description</span><textarea name="description" rows="7" placeholder="Detailed product story…">{{ old('description',$product->description) }}</textarea></label>
  <div class="form-grid two">
    <label class="field"><span>Fit notes</span><textarea name="fit_notes" rows="4">{{ old('fit_notes',$product->fit_notes) }}</textarea></label>
    <div class="common-copy-card"><span class="field-title">COMMON CARE + SHIPPING</span><strong>Managed centrally</strong><p>Care instructions and shipping notes are shared across the catalogue. Edit them once from Settings and every product inherits the latest version.</p><a href="{{ route('admin.settings.index') }}">OPEN COMMERCE SETTINGS →</a></div>
  </div>
</section>

<div class="sticky-save"><span><b>{{ $product->exists?'EDITING':'CREATING' }}</b> · {{ $product->name ?: 'New Tishla piece' }}</span><button class="btn btn-dark" type="submit">SAVE CATALOGUE ITEM <span>↗</span></button></div>
</form>

<aside class="studio-side">
  <section class="studio-preview-card">
    <div class="preview-art"><span>T</span><small>LIVE PREVIEW</small></div>
    <span class="eyebrow">STOREFRONT CARD</span>
    <h2>{{ $product->name ?: 'Your next Tishla piece' }}</h2>
    <p>{{ $product->short_description ?: 'A refined product story will appear here.' }}</p>
    <strong>₹{{ number_format((float)($product->base_price ?: 0),0) }}</strong>
    <small class="preview-note">Use real product media from Media Studio after saving the catalogue item.</small>
  </section>
  <section class="studio-tip"><span>✦</span><div><b>CATALOGUE TIP</b><p>Keep master values controlled, and keep shared customer messaging in Settings. Use variants only for purchasable options.</p></div></section>
</aside>
</section>
@endsection
