@extends('layouts.admin')
@section('content')
<div class="admin-page-head"><div><span class="eyebrow">INVENTORY</span><h1>Stock overview</h1><p>Monitor on-hand, reserved and available units by location.</p></div></div>

<section class="inventory-stats">
@foreach([['SKUs TRACKED',$stats['skus']],['UNITS ON HAND',$stats['units']],['UNITS RESERVED',$stats['reserved']],['LOW STOCK',$stats['low']]] as $stat)<div class="inventory-stat"><span>{{ $stat[0] }}</span><strong>{{ number_format($stat[1]) }}</strong></div>@endforeach
</section>

<section class="admin-card inventory-toolbar"><form><div><input name="q" value="{{ request('q') }}" placeholder="Search product, variant or SKU…"></div><select name="location"><option value="">All locations</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(request('location')===$location->id)>{{ $location->name }}</option>@endforeach</select><label class="toolbar-check"><input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))> Low stock only</label><button class="btn btn-light">FILTER</button></form></section>

<section class="admin-card inventory-table-card"><div class="table-wrap"><table><thead><tr><th>Product / Variant</th><th>Location</th><th>On hand</th><th>Reserved</th><th>Available</th><th>Reorder</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($stock as $row)
@php($available=(int)$row->on_hand-(int)$row->reserved)
<tr><td><strong>{{ $row->product_name }}</strong><small>{{ $row->variant_name }} · {{ $row->variant_sku }}</small></td><td>{{ $row->location_name }}<small>{{ $row->location_code }}</small></td><td>{{ $row->on_hand }}</td><td>{{ $row->reserved }}</td><td><strong>{{ $available }}</strong></td><td>{{ $row->reorder_level }}</td><td><span class="inventory-pill {{ $available <= 0 ? 'out' : ($available <= $row->reorder_level ? 'low' : 'ok') }}">{{ $available <= 0 ? 'OUT' : ($available <= $row->reorder_level ? 'LOW' : 'HEALTHY') }}</span></td><td><button class="text-button" type="button" data-stock-open='@json(['variant_id'=>$row->variant_id,'location_id'=>$row->location_id,'name'=>$row->product_name.' · '.$row->variant_name])'>Adjust</button></td></tr>
@empty<tr><td colspan="8"><div class="empty">No inventory records yet. Create variants and add stock below.</div></td></tr>@endforelse
</tbody></table></div>{{ $stock->links() }}</section>

<section class="admin-card stock-adjust-card"><div><span class="eyebrow">STOCK ADJUSTMENT</span><h2>Record stock movement</h2><p>Every adjustment is recorded for audit history.</p></div><form method="post" action="{{ route('admin.inventory.adjust') }}" class="form-grid three" data-stock-form>@csrf<input type="hidden" name="variant_id"><label class="field"><span>Location</span><select name="location_id" required><option value="">Select location</option>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></label><label class="field"><span>Quantity change</span><input type="number" name="quantity" placeholder="+25 or -2" required></label><label class="field"><span>Reorder level</span><input type="number" name="reorder_level" min="0" placeholder="5"></label><label class="field" style="grid-column:1/-1"><span>Reason</span><input name="reason" placeholder="New stock received, damaged piece, stock count correction…"></label><div style="grid-column:1/-1;display:flex;justify-content:flex-end"><button class="btn btn-dark">RECORD MOVEMENT ↗</button></div></form></section>
@endsection
