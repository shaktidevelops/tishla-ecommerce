@extends('layouts.storefront')
@section('content')
<section class="success-shell"><div class="success-mark">✓</div><span class="eyebrow">ORDER CONFIRMED</span><h1>Thank you.<br><em>It's on its way to being yours.</em></h1><p>Your order <strong>{{ $order->order_number }}</strong> has been placed successfully.</p><div class="success-total">₹{{ number_format((float)$order->grand_total,0) }}</div><div class="success-actions"><a class="btn btn-dark" href="{{ route('track-order',['order_number'=>$order->order_number]) }}">TRACK ORDER <span>↗</span></a><a class="btn btn-light" href="{{ route('shop') }}">CONTINUE SHOPPING</a></div></section>
@endsection
