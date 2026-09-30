@extends('layouts.admin')
@section('content')
<div class="admin-page-head admin-dashboard-head">
  <div>
    <span class="eyebrow">DASHBOARD</span>
    <h1>Store overview</h1>
    <p>Sales, catalogue, orders and stock — the operational picture at a glance.</p>
  </div>
  <div class="head-actions">
    <a class="btn btn-light" href="{{ route('admin.inventory.index') }}">INVENTORY</a>
    <a class="btn btn-dark" href="{{ route('admin.products.create') }}">ADD PRODUCT</a>
  </div>
</div>

<section class="dashboard-summary">
  <div class="dashboard-summary-main">
    <span class="eyebrow">TODAY</span>
    <strong>₹{{ number_format($todayRevenue,0) }}</strong>
    <p>Revenue recorded today</p>
  </div>
  <div class="dashboard-summary-side">
    <span><b>30 days</b> ₹{{ number_format($revenue30,0) }}</span>
    <span><b>Avg. order</b> ₹{{ number_format($averageOrder,0) }}</span>
    <span><b>Fulfilment queue</b> {{ $pendingOrders }}</span>
  </div>
</section>

<section class="kpi-grid dashboard-kpis">
  <div class="kpi"><span>PRODUCTS</span><strong>{{ number_format($activeProducts) }}</strong><small>Active on storefront</small></div>
  <div class="kpi"><span>CUSTOMERS</span><strong>{{ number_format($customers) }}</strong><small>Active customer records</small></div>
  <div class="kpi"><span>ORDERS · 30D</span><strong>{{ number_format($orders30) }}</strong><small>All order states</small></div>
  <div class="kpi"><span>LOW STOCK</span><strong>{{ number_format($lowStock) }}</strong><small>At or below reorder level</small></div>
</section>

<div class="dashboard-grid">
  <section class="admin-card dashboard-panel dashboard-chart-panel">
    <div class="panel-head"><div><span class="eyebrow">SALES</span><h2>Monthly revenue</h2></div><span class="panel-meta">12 months</span></div>
    @php $maxRevenue=max($monthlyRevenue?:[0]); @endphp
    @if($maxRevenue>0)
      <div class="chart-wrap chart-wrap-pro" aria-label="Monthly revenue">
        @foreach($monthlyRevenue as $i=>$value)
          @php $height=max(4,round(($value/$maxRevenue)*100)); @endphp
          <div class="chart-bar"><em>₹{{ number_format($value/1000,1) }}k</em><span style="height:{{ $height }}%"></span><small>{{ $monthLabels[$i] }}</small></div>
        @endforeach
      </div>
    @else
      <div class="empty-chart">Revenue data will appear here as orders are placed.</div>
    @endif
  </section>

  <section class="admin-card dashboard-panel">
    <div class="panel-head"><div><span class="eyebrow">ORDER PIPELINE</span><h2>Current order flow</h2></div><a href="{{ route('admin.orders.index') }}">View orders →</a></div>
    <div class="status-list">
      @forelse($statusCounts as $status=>$count)
        <div class="status-row"><span>{{ ucwords(str_replace('_',' ',$status)) }}</span><div class="status-track"><div class="status-fill" style="width:{{ max(4,round(($count/max($statusCounts?:[1]))*100)) }}%"></div></div><b>{{ $count }}</b></div>
      @empty
        <div class="empty">No orders yet.</div>
      @endforelse
    </div>
  </section>

  <section class="admin-card dashboard-panel">
    <div class="panel-head"><div><span class="eyebrow">TOP PRODUCTS</span><h2>Best movers</h2></div><a href="{{ route('admin.products.index') }}">Open catalogue →</a></div>
    <div class="top-products">
      @forelse($topProducts as $i=>$product)
        <div class="top-product"><span class="top-product-rank">{{ sprintf('%02d',$i+1) }}</span><div><b>{{ $product->product_name }}</b><small>{{ number_format((int)$product->units) }} units</small></div><strong>₹{{ number_format((float)$product->sales,0) }}</strong></div>
      @empty
        <div class="empty">Sales ranking appears after orders are placed.</div>
      @endforelse
    </div>
  </section>

  <section class="admin-card dashboard-panel">
    <div class="panel-head"><div><span class="eyebrow">RECENT ORDERS</span><h2>Latest activity</h2></div><a href="{{ route('admin.orders.index') }}">View all →</a></div>
    <div class="admin-list">
      @forelse($recentOrders as $order)
        <a class="admin-list-row" href="{{ route('admin.orders.show',$order) }}">
          <span><b>{{ $order->order_number }}</b><small>{{ $order->customer_name }}</small></span>
          <span><b>₹{{ number_format((float)$order->grand_total,0) }}</b><small>{{ ucwords(str_replace('_',' ',$order->status)) }}</small></span>
        </a>
      @empty
        <div class="empty">No orders yet.</div>
      @endforelse
    </div>
  </section>
</div>

<section class="admin-card quick-actions-panel">
  <div class="panel-head"><div><span class="eyebrow">QUICK ACTIONS</span><h2>Common tasks</h2></div></div>
  <div class="quick-actions-grid">
    <a href="{{ route('admin.products.create') }}"><span>01</span><b>Add product</b><small>Create one catalogue item</small></a>
    <a href="{{ route('admin.products.index') }}"><span>02</span><b>Bulk catalogue</b><small>Import, update or delete in volume</small></a>
    <a href="{{ route('admin.inventory.index') }}"><span>03</span><b>Adjust stock</b><small>Receive or correct inventory</small></a>
    <a href="{{ route('admin.media.index') }}"><span>04</span><b>Upload media</b><small>Attach product imagery</small></a>
    <a href="{{ route('admin.masters.index') }}"><span>05</span><b>Maintain masters</b><small>Keep catalogue values consistent</small></a>
    <a href="{{ route('admin.settings.index') }}"><span>06</span><b>Store settings</b><small>Tax, shipping and checkout</small></a>
  </div>
</section>
@endsection