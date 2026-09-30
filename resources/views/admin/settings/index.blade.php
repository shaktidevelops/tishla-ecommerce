@extends('layouts.admin')
@section('content')
@php
$store=$settings['store']->value??[];
$tax=$settings['tax']->value??[];
$checkout=$settings['checkout']->value??[];
$defaults=$settings['commerce_defaults']->value??[];
@endphp

<div class="admin-page-head">
  <div><span class="eyebrow">STORE CONFIGURATION</span><h1>Store settings</h1><p>Manage store identity, checkout defaults and shared customer information.</p></div>
</div>

<form method="post" action="{{ route('admin.settings.update') }}" class="settings-shell">
@csrf @method('PUT')

<section class="admin-card settings-card">
  <div class="settings-section-head"><div><span class="eyebrow">01 · BRAND & CONTACT</span><h2>Store identity.</h2></div><span>FOUNDATION</span></div>
  <div class="form-grid two">
    <label class="field"><span>Store name</span><input name="store_name" value="{{ old('store_name',$store['name']??env('TISHLA_STORE_NAME')) }}"></label>
    <label class="field"><span>Domain</span><input name="domain" value="{{ old('domain',$store['domain']??env('TISHLA_DOMAIN')) }}"></label>
    <label class="field"><span>Support email</span><input type="email" name="support_email" value="{{ old('support_email',$store['support_email']??env('TISHLA_SUPPORT_EMAIL')) }}"></label>
    <label class="field"><span>WhatsApp</span><input name="support_whatsapp" value="{{ old('support_whatsapp',$store['support_whatsapp']??env('TISHLA_WHATSAPP')) }}"></label>
  </div>
</section>

<section class="admin-card settings-card">
  <div class="settings-section-head"><div><span class="eyebrow">02 · TAX & DELIVERY</span><h2>Checkout & commercial defaults.</h2></div><span>CHECKOUT</span></div>
  <div class="form-grid three">
    <label class="field"><span>GST rate %</span><input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate',$tax['gst_rate']??env('TISHLA_GST_RATE',5)) }}"></label>
    <label class="field"><span>Default shipping charge</span><input type="number" step="0.01" name="shipping_charge" value="{{ old('shipping_charge',$tax['default_shipping_charge']??0) }}"></label>
    <div class="settings-note"><strong>One source of truth</strong><p>These values are used as platform defaults. Future customer-tier and promotion rules can sit above them.</p></div>
  </div>
  <div class="toggle-grid">
    <label class="toggle"><input type="checkbox" name="guest_checkout" value="1" @checked($checkout['guest_checkout']??true)><span><b>Guest checkout</b><small>Allow customers to buy without creating an account.</small></span></label>
    <label class="toggle"><input type="checkbox" name="cod_enabled" value="1" @checked($checkout['cod_enabled']??true)><span><b>Cash on delivery</b><small>Keep COD available at checkout when operationally permitted.</small></span></label>
    <label class="toggle"><input type="checkbox" name="online_payment_enabled" value="1" @checked($checkout['online_payment_enabled']??false)><span><b>Online payments</b><small>Enable only after the payment gateway is configured and tested.</small></span></label>
  </div>
</section>

<section class="admin-card settings-card">
  <div class="settings-section-head"><div><span class="eyebrow">03 · SHARED STORE COPY</span><h2>Care & shipping</h2></div><span>APPLIES TO ALL PRODUCTS</span></div>
  <div class="shared-copy-banner"><span>✦</span><div><strong>Shared across catalogue</strong><p>These messages are maintained once here and displayed across product pages.</p></div></div>
  <div class="form-grid two">
    <label class="field"><span>Care instructions</span><textarea name="care_instructions" rows="9" required>{{ old('care_instructions',$defaults['care_instructions']??'') }}</textarea></label>
    <label class="field"><span>Shipping notes</span><textarea name="shipping_notes" rows="9" required>{{ old('shipping_notes',$defaults['shipping_notes']??'') }}</textarea></label>
  </div>
</section>

<div class="sticky-save settings-save"><span><b>TISHLA GLOBAL SETTINGS</b> · Changes affect shared storefront defaults.</span><button class="btn btn-dark">SAVE ALL SETTINGS <span>↗</span></button></div>
</form>
@endsection