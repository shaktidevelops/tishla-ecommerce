@extends('layouts.admin')
@section('content')
<div class="admin-page-head">
  <div><span class="eyebrow">CATALOGUE OPERATIONS</span><h1>Product catalogue</h1><p>One workspace for individual editing, bulk data operations and catalogue hygiene.</p></div>
  <div class="head-actions"><a class="btn btn-light" href="{{ route('admin.masters.index') }}">MASTERS</a><a class="btn btn-light" href="{{ route('admin.products.export') }}">EXPORT CSV ↓</a><a class="btn btn-dark" href="{{ route('admin.products.create') }}">NEW PRODUCT +</a></div>
</div>

@if(session('import_errors'))<section class="admin-card import-errors"><div class="section-head"><div><span class="eyebrow">IMPORT REPORT</span><h2>Rows needing attention</h2></div></div>@foreach(session('import_errors') as $error)<div>{{ $error }}</div>@endforeach</section>@endif

<section class="catalogue-ops-grid">
  <div class="admin-card bulk-card">
    <div class="bulk-card-head"><span class="ops-icon">↑</span><div><span class="eyebrow">BULK UPLOAD</span><h2>Create from CSV</h2><p>Add new products in volume. Existing SKUs are rejected so uploads cannot silently overwrite the catalogue.</p></div></div>
    <form method="post" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="bulk-form">@csrf<input type="file" name="file" accept=".csv,.txt" required><button class="btn btn-dark">UPLOAD CSV</button></form>
    <a class="small-link" href="{{ route('admin.products.template') }}">Download CSV template →</a>
  </div>
  <div class="admin-card bulk-card">
    <div class="bulk-card-head"><span class="ops-icon">↻</span><div><span class="eyebrow">BULK UPDATE</span><h2>Refresh existing products</h2><p>Match by SKU and update product-level data. Variant columns may also update one matching variant.</p></div></div>
    <form method="post" action="{{ route('admin.products.bulk-update') }}" enctype="multipart/form-data" class="bulk-form">@csrf<input type="file" name="file" accept=".csv,.txt" required><button class="btn btn-dark">UPDATE CSV</button></form>
    <a class="small-link" href="{{ route('admin.products.export') }}">Export current catalogue →</a>
  </div>
</section>

<section class="admin-card catalogue-list-card">
  <div class="catalogue-toolbar">
    <form class="catalogue-filter-form">
      <input name="q" value="{{ request('q') }}" placeholder="Search SKU or product…">
      <select name="status"><option value="">All status</option>@foreach(['draft','active','archived'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
      <select name="department"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department')===$department->id)>{{ $department->name }}</option>@endforeach</select>
      <button class="btn btn-light">FILTER</button>
    </form>
    <span class="catalogue-total">{{ number_format($products->total()) }} products</span>
  </div>

  <form method="post" action="{{ route('admin.products.bulk-delete') }}" data-bulk-delete-form>
    @csrf
    <div class="bulk-action-bar" data-bulk-bar>
      <label><input type="checkbox" data-select-all> Select page</label>
      <span data-selected-count>0 selected</span>
      <button class="danger-button" type="submit" data-bulk-delete disabled>DELETE SELECTED</button>
    </div>
    <div class="table-wrap">
      <table class="catalogue-table">
        <thead><tr><th class="check-cell"></th><th>Product</th><th>Classification</th><th>Pricing</th><th>Variants</th><th>Status</th><th>Visibility</th><th></th></tr></thead>
        <tbody>
        @forelse($products as $product)
        <tr>
          <td class="check-cell"><input type="checkbox" name="ids[]" value="{{ $product->id }}" data-row-select></td>
          <td><div class="product-table-identity">@if($product->images->first())<img src="{{ $product->images->first()->public_url }}" alt="">@else<div class="mini-fallback">T</div>@endif<div><strong>{{ $product->name }}</strong><small>{{ $product->sku }}</small></div></div></td>
          <td><strong>{{ $product->department?->name ?: '—' }}</strong><small>{{ $product->product_type ?: 'No type' }} · {{ $product->shoot_type ?: 'No shoot' }}</small></td>
          <td><strong>₹{{ number_format((float)$product->base_price,0) }}</strong><small>GST {{ number_format((float)$product->gst_rate,2) }}%</small></td>
          <td><strong>{{ $product->variants->count() }}</strong><small>purchasable options</small></td>
          <td><span class="catalogue-status {{ $product->status }}">{{ strtoupper($product->status) }}</span></td>
          <td>{{ $product->featured ? 'Featured' : 'Standard' }}</td>
          <td><a class="row-action" href="{{ route('admin.products.edit',$product) }}">EDIT ↗</a></td>
        </tr>
        @empty
        <tr><td colspan="8"><div class="empty">No products match these filters.</div></td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </form>
  <div class="pagination-wrap">{{ $products->links() }}</div>
</section>
@endsection