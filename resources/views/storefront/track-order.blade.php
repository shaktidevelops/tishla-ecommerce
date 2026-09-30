@extends('layouts.storefront')
@section('content')
<section class="inner-hero compact-hero"><div><span class="eyebrow">TISHLA CONCIERGE</span><h1>Track your<br><em>order.</em></h1><p>Enter your Tishla order number and the email or phone used at checkout.</p></div><div class="inner-hero-mark">03</div></section>
<section class="support-shell">
  <div class="support-card order-lookup-card"><div class="support-card-head"><span class="eyebrow">ORDER LOOKUP</span><span>PRIVATE · SECURE</span></div><form method="get" action="{{ route('track-order') }}" class="support-form"><label>Order number<input name="order_number" value="{{ request('order_number') }}" placeholder="e.g. TSH-2026-0001" required></label><div class="support-grid-2"><label>Email<input type="email" name="email" value="{{ request('email') }}" placeholder="you@example.com"></label><label>Phone<input name="phone" value="{{ request('phone') }}" placeholder="10 digit mobile number"></label></div><p class="form-note">Use either email or phone. We never display another customer's order.</p><button class="btn btn-dark" type="submit">FIND MY ORDER <span>↗</span></button></form></div>
  @if(request()->filled('order_number'))
    @if($order)
      @php $steps=['pending_payment'=>'Order placed','paid'=>'Payment confirmed','confirmed'=>'Confirmed','processing'=>'Preparing','packed'=>'Packed','shipped'=>'Shipped','delivered'=>'Delivered']; $statuses=array_keys($steps); $current=array_search($order->status,$statuses,true); if($current===false)$current=$order->status==='cancelled'?-1:0; @endphp
      <div class="tracking-card"><div class="tracking-top"><div><span class="eyebrow">ORDER {{ $order->order_number }}</span><h2>{{ $order->customer_name }}</h2></div><strong>₹{{ number_format((float)$order->grand_total,2) }}</strong></div>
        @if($order->status==='cancelled')<div class="status-banner danger">This order has been cancelled.</div>@elseif(in_array($order->status,['payment_failed','refunded','returned'],true))<div class="status-banner danger">Current status: {{ str_replace('_',' ',ucfirst($order->status)) }}.</div>@else<div class="tracking-line">@foreach($steps as $key=>$label)<div class="track-step {{ $current>=array_search($key,$statuses,true)?'done':'' }}"><span>{{ $current>=array_search($key,$statuses,true)?'✓':'•' }}</span><small>{{ $label }}</small></div>@endforeach</div>@endif
        <div class="tracking-details"><div><span>PLACED</span><b>{{ optional($order->placed_at)->format('d M Y, h:i A') ?: '—' }}</b></div><div><span>PAYMENT</span><b>{{ strtoupper($order->payment_method ?: 'Pending') }}</b></div><div><span>ITEMS</span><b>{{ $order->items->sum('quantity') }}</b></div><div><span>SHIPMENT</span><b>{{ optional($order->shipments->first())->tracking_number ?: 'Preparing' }}</b></div></div>
      </div>
    @else
      <div class="support-card lookup-empty"><div class="empty-icon">⌁</div><h2>We couldn't find that order.</h2><p>Check the order number and use the same email or phone entered during checkout.</p></div>
    @endif
  @endif
</section>
@endsection
