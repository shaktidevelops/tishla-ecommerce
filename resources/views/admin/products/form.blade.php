@extends('layouts.admin')@section('content')
<div class="admin-page-head"><div><span class="eyebrow">CATALOGUE STUDIO</span><h1>{{ $product->exists?'Edit product':'New product' }}</h1><p>Structured product data ready for variants, media and inventory.</p></div><a class="btn btn-light" href="{{ route('admin.products.index') }}">BACK</a></div>
<section class="admin-card"><form method="post" action="{{ $product->exists?route('admin.products.update',$product):route('admin.products.store') }}">@csrf @if($product->exists)@method('PUT')@endif
<div class="form-grid three">
<label class="field"><span>SKU</span><input name="sku" value="{{ old('sku',$product->sku) }}" required></label>
<label class="field"><span>Slug</span><input name="slug" value="{{ old('slug',$product->slug) }}"></label>
<label class="field"><span>Name</span><input name="name" value="{{ old('name',$product->name) }}" required></label>
<label class="field"><span>Fabric</span><input name="fabric" value="{{ old('fabric',$product->fabric) }}"></label>
<label class="field"><span>Product type</span><input name="product_type" value="{{ old('product_type',$product->product_type) }}"></label>
<label class="field"><span>Shoot type</span><input name="shoot_type" value="{{ old('shoot_type',$product->shoot_type) }}"></label>
<label class="field"><span>Retail price</span><input type="number" step="0.01" name="base_price" value="{{ old('base_price',$product->base_price) }}"></label>
<label class="field"><span>GST %</span><input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate',$product->gst_rate) }}" required></label>
<label class="field"><span>Minimum order qty</span><input type="number" name="min_order_qty" value="{{ old('min_order_qty',$product->min_order_qty?:1) }}" min="1" required></label>
<label class="field"><span>Department</span><select name="department_id"><option value="">Select</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id',$product->department_id)==$department->id)>{{ $department->name }}</option>@endforeach</select></label>
<label class="field"><span>Status</span><select name="status">@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(old('status',$product->status)===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
<label class="toggle"><input type="checkbox" name="featured" value="1" @checked(old('featured',$product->featured))> Featured product</label>
</div>
<label class="field"><span>Product badge</span><input name="product_badge" value="{{ old('product_badge',$product->product_badge) }}" placeholder="New / Bestseller / Exclusive"></label>
<label class="field"><span>Short description</span><textarea name="short_description">{{ old('short_description',$product->short_description) }}</textarea></label>
<label class="field"><span>Description</span><textarea name="description" rows="8">{{ old('description',$product->description) }}</textarea></label>
@if($errors->any())<div class="error-box">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="form-actions"><button class="btn btn-dark">SAVE PRODUCT</button></div></form></section>@endsection