@extends('layouts.storefront')
@section('content')
<section class="inner-hero compact-hero"><div><span class="eyebrow">CHECKOUT</span><h1>One last<br><em>step.</em></h1><p>Enter your details and choose your payment method.</p></div><div class="inner-hero-mark">02</div></section>

<section class="checkout-shell">
  <form method="post" action="{{ route('checkout.store') }}" class="checkout-form">
    @csrf
    <div class="checkout-step"><div class="step-number">01</div><div class="checkout-block"><div class="block-head"><span class="eyebrow">CONTACT</span><small>Who should we reach?</small></div><label class="field"><span>Full name</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label><div class="two-col"><label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label><label class="field"><span>Phone</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel" required></label></div></div></div>
    <div class="checkout-step"><div class="step-number">02</div><div class="checkout-block"><div class="block-head"><span class="eyebrow">DELIVERY</span><small>Where should we send it?</small></div><label class="field"><span>Address</span><input name="line1" value="{{ old('line1') }}" autocomplete="address-line1" required></label><label class="field"><span>Apartment / landmark</span><input name="line2" value="{{ old('line2') }}" autocomplete="address-line2"></label><div class="two-col"><label class="field"><span>Area</span><input name="area" value="{{ old('area') }}"></label><label class="field"><span>City</span><input name="city" value="{{ old('city','Surat') }}" autocomplete="address-level2" required></label></div><div class="two-col"><label class="field"><span>State</span><input name="state" value="{{ old('state','Gujarat') }}" autocomplete="address-level1" required></label><label class="field"><span>PIN code</span><input name="postal_code" value="{{ old('postal_code') }}" autocomplete="postal-code" required></label></div><label class="field"><span>Country</span><input name="country" value="{{ old('country','India') }}" autocomplete="country" required></label></div></div>
    <div class="checkout-step"><div class="step-number">03</div><div class="checkout-block"><div class="block-head"><span class="eyebrow">PAYMENT</span><small>Choose how you'd like to pay.</small></div><label class="payment-option"><input type="radio" name="payment_method" value="cod" checked><span><strong>Cash on delivery</strong><small>Pay when your order arrives, where available.</small></span><b>COD</b></label><label class="payment-option is-disabled"><input type="radio" name="payment_method" value="online" disabled><span><strong>Online payment</strong><small>Razorpay integration is prepared for a later release.</small></span><b>LATER</b></label></div></div>
    @if($errors->any())<div class="error-box">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <button class="btn btn-dark wide place-order-btn" type="submit">PLACE ORDER <span>↗</span></button>
    <p class="checkout-note">By placing the order, you confirm that the contact and shipping details are accurate.</p>
  </form>

  <aside class="order-summary-card checkout-summary">
    <div class="commerce-head"><span class="eyebrow">ORDER SUMMARY</span><strong>{{ collect($cart)->sum('quantity') }} ITEMS</strong></div>
    <div class="checkout-items">
      @foreach($cart as $item)
        <div class="checkout-item"><img src="{{ $item['image_url'] }}" alt=""><div><strong>{{ $item['name'] }}</strong><small>{{ $item['variant_name'] ?: 'Standard option' }} · Qty {{ $item['quantity'] }}</small></div><b>₹{{ number_format($item['price']*$item['quantity'],0) }}</b></div>
      @endforeach
    </div>
    <div class="summary-total"><span>Subtotal</span><b>₹{{ number_format(collect($cart)->sum(fn($i)=>$i['price']*$i['quantity']),0) }}</b></div>
    <div class="summary-note"><span>NEED HELP?</span><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">CHAT WITH TISHLA ↗</a></div>
  </aside>
</section>
@endsection
