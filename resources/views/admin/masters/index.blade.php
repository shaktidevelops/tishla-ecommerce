@extends('layouts.admin')
@section('content')
<div class="admin-page-head"><div><span class="eyebrow">CATALOGUE MASTERS</span><h1>Product data standards</h1><p>Control the values used across catalogue entry, imports, filters and reporting.</p></div></div>

<div class="master-grid">
@php
$masterSections=[
['type'=>'department','title'=>'Departments','items'=>$departments,'hint'=>'Primary merchandising areas.'],
['type'=>'product_type','title'=>'Product Types','items'=>$productTypes,'hint'=>'Standard product classifications.'],
['type'=>'shoot_type','title'=>'Shoot Types','items'=>$shootTypes,'hint'=>'Standard content / photography classifications.'],
];
@endphp
@foreach($masterSections as $section)
<section class="master-card admin-card">
  <div class="master-card-head"><div><span class="eyebrow">MASTER</span><h2>{{ $section['title'] }}</h2><p>{{ $section['hint'] }}</p></div><span class="master-count">{{ $section['items']->count() }} values</span></div>
  <form class="master-add-form" method="post" action="{{ route('admin.masters.store',$section['type']) }}">@csrf<input name="name" placeholder="Add {{ strtolower($section['title']) }} value…" required><input name="sort_order" type="number" min="0" value="10" placeholder="Order"><button class="btn btn-dark">ADD</button></form>
  <div class="master-list">
  @forelse($section['items'] as $item)
    <form class="master-row" method="post" action="{{ route('admin.masters.update',[$section['type'],$item->id]) }}">
      @csrf @method('PATCH')
      <input name="name" value="{{ $item->name }}" required>
      <input name="sort_order" type="number" min="0" value="{{ $item->sort_order }}" aria-label="Sort order">
      <label class="master-active"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Active</label>
      <button class="text-button">SAVE</button>
      <button class="danger-mini" formaction="{{ route('admin.masters.destroy',[$section['type'],$item->id]) }}" formmethod="post" onclick="return confirm('Remove this master value?')">@method('DELETE')×</button>
    </form>
  @empty<div class="empty">No values yet.</div>@endforelse
  </div>
</section>
@endforeach
</div>
<section class="admin-card master-guidance"><span class="eyebrow">DATA DISCIPLINE</span><h2>Why masters matter.</h2><p>Controlled values prevent spelling drift and make catalogue imports, storefront filters and future reporting dependable.</p></section>
@endsection