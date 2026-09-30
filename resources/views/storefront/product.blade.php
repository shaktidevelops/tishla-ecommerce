@extends('layouts.storefront')

@section('content')
@php
  $gallery = $product->images;
  $primary = $gallery->first();
  $whatsapp = preg_replace('/\D+/', '', env('TISHLA_WHATSAPP','919574716712'));
  $enquiry = rawurlencode("Hello Tishla by Purnika Sales, I am interested in: {$product->name} (SKU: {$product->sku}). Please share availability/details.");
  $careInstructions = $commerceDefaults['care_instructions'] ?? 'Care information is shared by Tishla through the product care guidance.';
  $shippingNotes = $commerceDefaults['shipping_notes'] ?? 'Delivery timelines vary by destination, availability and courier service.';
  $selectedVariant = $product->variants->first();
  $startPrice = (float)($selectedVariant?->price ?: $product->base_price);
@endphp

<section class="product-topbar"><a href="{{ route('shop') }}">SHOP</a><span> / </span><a href="{{ route('shop',['department'=>$product->department?->slug]) }}">{{ strtoupper($product->department?->name ?? 'COLLECTION') }}</a><span> / </span><span>{{ $product->name }}</span></section>

<section class="product-layout">
  <div class="product-media-column">
    <div class="product-main-image">
      @if($primary)<img src="{{ $primary->public_url }}" alt="{{ $primary->alt_text ?: $product->name }}" data-main-image>@else<div class="image-fallback"><span>T</span></div>@endif
      <span class="product-image-stamp">TISHLA<br><small>{{ $product->sku }}</small></span>
    </div>
    @if($gallery->count()>1)
      <div class="product-thumbs">
        @foreach($gallery as $image)
          <button type="button" class="product-thumb {{ $loop->first?'active':'' }}" data-product-thumb data-image="{{ $image->public_url }}" data-alt="{{ $image->alt_text ?: $product->name }}"><img src="{{ $image->public_url }}" alt="" loading="lazy"></button>
        @endforeach
      </div>
    @endif
  </div>

  <div class="product-info">
    <div class="product-info-kicker"><span class="eyebrow">{{ $product->department?->name }}</span>@if($product->product_badge)<span class="detail-badge">{{ $product->product_badge }}</span>@endif</div>
    <h1>{{ $product->name }}</h1>
    <div class="product-sku-row"><span>SKU · {{ $product->sku }}</span><span>{{ $product->product_type ?: 'TISHLA EDIT' }}</span></div>

    <div class="product-price" data-product-price="₹{{ number_format($startPrice,0) }}" data-price-value="{{ $startPrice }}">
      ₹{{ number_format($startPrice,0) }}
      @if($selectedVariant && $selectedVariant->compare_at_price && (float)$selectedVariant->compare_at_price>$startPrice)<del data-compare-price>₹{{ number_format((float)$selectedVariant->compare_at_price,0) }}</del>@else<del data-compare-price hidden></del>@endif
      <small>INCLUSIVE OF APPLICABLE TAX</small>
    </div>

    @if($product->short_description)<p class="product-intro-copy">{{ $product->short_description }}</p>@endif

    <div class="trust-row">
      <span><i>✦</i> Curated in Surat</span>
      <span><i>✦</i> Indiawide delivery</span>
      <span><i>✦</i> COD available</span>
    </div>

    <div class="product-divider"></div>

    <form method="post" action="{{ route('cart.add',$product) }}" class="buy-box">
      @csrf
      @if($product->variants->count())
        <div class="variant-block">
          <div class="field-label-row"><strong>SELECT YOUR OPTION</strong><span>Choose one to continue</span></div>
          <div class="variant-list">
            @foreach($product->variants as $variant)
              @php $variantPrice=(float)($variant->price ?: $product->base_price); $variantCompare=(float)($variant->compare_at_price ?: 0); @endphp
              <label class="variant-option">
                <input type="radio" name="variant_id" value="{{ $variant->id }}" data-price="{{ $variantPrice }}" data-compare="{{ $variantCompare }}" {{ $loop->first?'checked':'' }}>
                <span class="variant-option-copy">
                  @if($variant->color_hex)<i class="color-dot" style="--variant-color:{{ $variant->color_hex }}"></i>@endif
                  <strong>{{ $variant->name }}</strong>
                  @if($variant->size_name)<small>{{ $variant->size_name }}</small>@endif
                  @if($variant->color_name)<small>{{ $variant->color_name }}</small>@endif
                </span>
                <b>₹{{ number_format($variantPrice,0) }}</b>
              </label>
            @endforeach
          </div>
        </div>
      @endif

      <div class="quantity-row"><label class="quantity-field"><span>QTY</span><input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"></label><button class="btn btn-dark buy-button" type="submit">ADD TO BAG <span>↗</span></button></div>
    </form>

    <a class="whatsapp-enquiry" href="https://wa.me/{{ $whatsapp }}?text={{ $enquiry }}" target="_blank" rel="noopener noreferrer"><span class="wa-dot">◌</span><span><strong>Ask Tishla about this piece</strong><small>Availability · colour · sizing · styling help</small></span><b>↗</b></a>

    <div class="product-accordions">
      @if($product->description)<details open><summary>DETAILS <span>＋</span></summary><p>{{ $product->description }}</p></details>@endif
      @if($product->fabric)<details><summary>FABRIC &amp; CRAFT <span>＋</span></summary><p>{{ $product->fabric }}</p></details>@endif
      @if($product->fit_notes)<details><summary>FIT NOTES <span>＋</span></summary><p>{{ $product->fit_notes }}</p></details>@endif
      <details><summary>CARE <span>＋</span></summary><p>{{ $careInstructions }}</p></details>
      <details><summary>DELIVERY <span>＋</span></summary><p>{{ $shippingNotes }}</p></details>
    </div>
  </div>
</section>

<section class="product-benefits">
  <div><span>01</span><strong>Need a second opinion?</strong><p>Send the product to us on WhatsApp and ask anything before ordering.</p></div>
  <div><span>02</span><strong>Ordering for an occasion?</strong><p>Share the event date and we'll help with your selection where possible.</p></div>
  <div><span>03</span><strong>Want to see more?</strong><p>Browse your recently viewed pieces and keep building your edit.</p></div>
</section>

@if($recentProducts->count())
<section class="home-section related-section">
  <div class="section-heading-row"><div><span class="eyebrow">KEEP EXPLORING</span><h2>Recently viewed.</h2></div><a class="text-link" href="{{ route('shop') }}">SHOP COLLECTION <span>↗</span></a></div>
  <div class="product-rail">
    @foreach($recentProducts as $recent)
      @php $image=$recent->images->first(); $variant=$recent->variants->first(); @endphp
      <a class="rail-card reveal" href="{{ route('product',$recent->slug) }}"><div class="rail-image">@if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $recent->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif</div><div class="rail-meta"><span>{{ $recent->department?->name }}</span><strong>{{ $recent->name }}</strong><b>₹{{ number_format((float)($variant?->price ?: $recent->base_price),0) }}</b></div></a>
    @endforeach
  </div>
</section>
@endif
@endsection
