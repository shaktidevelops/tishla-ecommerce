@extends('layouts.storefront')
@section('content')
<section class="inner-hero compact-hero"><div><span class="eyebrow">YOUR BAG</span><h1>Almost<br><em>yours.</em></h1><p>Review your pieces, adjust quantities and continue when you're ready.</p></div><div class="inner-hero-mark">01</div></section>

<section class="commerce-shell">
  @if(count($cart))
    <div class="cart-column">
      <div class="commerce-head"><span class="eyebrow">SHOPPING BAG</span><strong>{{ collect($cart)->sum('quantity') }} {{ collect($cart)->sum('quantity')===1?'item':'items' }}</strong></div>
      <div class="cart-list">
        <form method="post" action="{{ route('cart.update') }}">
          @csrf @method('PUT')
          @foreach($cart as $key=>$item)
            <div class="cart-item">
              <a class="cart-image" href="{{ route('product',$item['slug']) }}"><img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"></a>
              <div class="cart-item-main"><div><span>{{ $item['sku'] }}</span><a href="{{ route('product',$item['slug']) }}"><strong>{{ $item['name'] }}</strong></a><small>{{ $item['variant_name'] ?: 'Standard option' }}</small></div><button class="remove-link remove-button" type="submit" formmethod="post" formaction="{{ route('cart.remove',$key) }}" name="_method" value="DELETE">Remove</button></div>
              <div class="cart-item-controls"><label><span>QTY</span><input type="number" name="quantity[{{ $key }}]" value="{{ $item['quantity'] }}" min="1" max="20"></label><b>₹{{ number_format($item['price']*$item['quantity'],0) }}</b></div>
            </div>
          @endforeach
          <button class="btn btn-light" type="submit">UPDATE BAG</button>
        </form>
      </div>
      <div class="cart-help"><span>✦</span><div><strong>Need a little help?</strong><p>Ask about styling, sizing, availability or your order on WhatsApp.</p></div><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">CHAT ↗</a></div>
    </div>

    <aside class="order-summary-card">
      <div class="commerce-head"><span class="eyebrow">SUMMARY</span><strong>INR</strong></div>
      <div class="summary-total"><span>Subtotal</span><b>₹{{ number_format(collect($cart)->sum(fn($i)=>$i['price']*$i['quantity']),0) }}</b></div>
      <div class="summary-line"><span>GST</span><small>Calculated at checkout</small></div>
      <div class="summary-line"><span>Shipping</span><small>Confirmed at checkout</small></div>
      <div class="summary-rule"></div>
      <a class="btn btn-dark wide" href="{{ route('checkout') }}">PROCEED TO CHECKOUT <span>↗</span></a>
      <a class="summary-continue" href="{{ route('shop') }}">← Continue shopping</a>
      <div class="summary-note"><span>SECURE SESSION BAG</span><p>Your bag is saved in this browser while you shop.</p></div>
    </aside>
  @else
    <div class="empty-state cart-empty"><div class="empty-icon">□</div><span class="eyebrow">YOUR BAG IS EMPTY</span><h2>Let's find<br><em>something special.</em></h2><p>Start with new arrivals, sarees or a full collection edit.</p><a class="btn btn-dark" href="{{ route('shop') }}">START SHOPPING <span>↗</span></a></div>
  @endif
</section>
@endsection
