@extends('layouts.storefront')

@section('content')
@php
  $gallery = $product->images;
  $primary = $gallery->first();
  $whatsapp = preg_replace('/\D+/', '', env('TISHLA_WHATSAPP','919574716712'));
  $enquiry = rawurlencode("Hello Tishla by Purnika Sales, I am interested in: {$product->name} (SKU: {$product->sku}). Please share availability/details.");
@endphp

<section class="product-breadcrumb">
  <a href="{{ route('home') }}">HOME</a><span> / </span><a href="{{ route('shop') }}">SHOP</a><span> / </span><span>{{ strtoupper($product->department?->name ?? 'COLLECTION') }}</span>
</section>

<section class="product-detail premium-product">
  <div class="product-gallery">
    <div class="product-large product-stage" data-product-stage>
      @if($primary)
        <img src="{{ $primary->public_url }}" alt="{{ $primary->alt_text ?: $product->name }}" data-main-image>
      @else
        <div class="image-fallback"><span>T</span></div>
      @endif
      <div class="product-stage-label">TISHLA / {{ $product->sku }}</div>
    </div>
    @if($gallery->count() > 1)
      <div class="product-thumbs" aria-label="Product gallery">
        @foreach($gallery as $image)
          <button type="button" class="product-thumb {{ $loop->first ? 'active' : '' }}" data-product-thumb data-image="{{ $image->public_url }}" data-alt="{{ $image->alt_text ?: $product->name }}" aria-label="View image {{ $loop->iteration }}">
            <img src="{{ $image->public_url }}" alt="" loading="lazy">
          </button>
        @endforeach
      </div>
    @endif
  </div>

  <div class="product-info">
    <div class="product-meta-line"><span class="eyebrow">{{ $product->department?->name }}</span>@if($product->product_badge)<span class="product-detail-badge">{{ $product->product_badge }}</span>@endif</div>
    <h1>{{ $product->name }}</h1>
    <div class="product-sku">SKU · {{ $product->sku }}</div>
    <div class="price">₹{{ number_format((float)$product->base_price,0) }} <span>INCLUSIVE OF APPLICABLE TAX</span></div>
    <p class="product-lead">{{ $product->short_description }}</p>

    <div class="trust-pills">
      <span>✦ PREMIUM CURATION</span>
      <span>✦ INDIAWIDE DELIVERY</span>
      <span>✦ WHATSAPP ASSISTANCE</span>
    </div>

    <div class="divider"></div>

    <form method="post" action="{{ route('cart.add',$product) }}" class="product-buy-form">
      @csrf

      @if($product->variants->count())
        <div class="field">
          <div class="field-label-row"><span>SELECT VARIANT</span><small>Choose your preferred option</small></div>
          <div class="variant-grid">
            @foreach($product->variants as $variant)
              <label class="variant-choice">
                <input type="radio" name="variant_id" value="{{ $variant->id }}" {{ $loop->first ? 'checked' : '' }}>
                <span>
                  <strong>{{ $variant->name }}</strong>
                  @if($variant->size_name)<small>{{ $variant->size_name }}</small>@endif
                  @if($variant->color_name)<small>{{ $variant->color_name }}</small>@endif
                  <b>₹{{ number_format((float)($variant->price ?: $product->base_price),0) }}</b>
                </span>
              </label>
            @endforeach
          </div>
        </div>
      @endif

      <div class="buy-row">
        <label class="quantity-field"><span>QTY</span><input type="number" name="quantity" value="1" min="1" max="20" aria-label="Quantity"></label>
        <button class="btn btn-dark buy-button" type="submit">ADD TO BAG <span>↗</span></button>
      </div>
    </form>

    <a class="whatsapp-enquiry" href="https://wa.me/{{ $whatsapp }}?text={{ $enquiry }}" target="_blank" rel="noopener noreferrer">
      <span class="whatsapp-mark">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.1 3.9A9.9 9.9 0 0 0 4.4 16.1L3 21l5-1.3A9.9 9.9 0 1 0 20.1 3.9ZM12 19.1c-1.5 0-2.9-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A7.9 7.9 0 1 1 12 19.1Zm4.4-5.8c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1-.2.3-.6.8-.7 1-.1.1-.3.2-.5.1-1.8-.9-3-1.6-4.1-3.7-.3-.5.3-.5.7-1.3.1-.2.1-.4 0-.5l-.4-1c-.1-.3-.3-.3-.5-.3h-.4c-.1 0-.4.1-.6.3-.6.6-.8 1.4-.8 2.2 0 .5.1 1 .3 1.4 0 .1 1.2 2.7 4.1 4.2 2.4 1.2 2.4.8 2.8.8.6 0 1.9-.8 2.1-1.6.1-.4.1-.7 0-.8Z" fill="currentColor"/></svg>
      </span>
      <span><strong>Ask on WhatsApp</strong><small>Personal assistance for this piece</small></span>
      <b>↗</b>
    </a>

    <div class="accordions">
      <details open><summary>DETAILS <span>+</span></summary><p>{{ $product->description }}</p></details>
      @if($product->fabric)<details><summary>FABRIC &amp; CRAFT <span>+</span></summary><p>{{ $product->fabric }}</p></details>@endif
      @if($product->fit_notes)<details><summary>FIT NOTES <span>+</span></summary><p>{{ $product->fit_notes }}</p></details>@endif
      @if($product->care_instructions)<details><summary>CARE <span>+</span></summary><p>{{ $product->care_instructions }}</p></details>@endif
      @if($product->shipping_notes)<details><summary>DELIVERY <span>+</span></summary><p>{{ $product->shipping_notes }}</p></details>@endif
    </div>
  </div>
</section>
@endsection