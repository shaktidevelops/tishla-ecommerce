@extends('layouts.storefront')

@section('content')
<section class="brand-hero">
  <picture>
    <img src="{{ asset('assets/tishla-banner.png') }}" alt="Tishla by Purnika Sales — Indian womenswear" fetchpriority="high">
  </picture>
  <div class="brand-hero-shade"></div>
  <div class="brand-hero-copy">
    <span class="eyebrow">TISHLA BY PURNIKA SALES · SURAT</span>
    <h1>Elegance<br><em>that speaks.</em></h1>
    <p>Indian occasionwear and contemporary silhouettes, curated for modern celebrations.</p>
    <div class="hero-actions">
      <a class="btn btn-dark" href="{{ route('shop') }}">SHOP THE COLLECTION <span>↗</span></a>
      <a class="btn btn-light hero-light-btn" href="{{ route('shop',['department'=>'sarees']) }}">EXPLORE SAREES</a>
    </div>
  </div>
  <div class="brand-hero-note">NEW EDIT · 2026</div>
</section>

<div class="marquee"><div>NEW ARRIVALS <i>✦</i> FESTIVE EDIT <i>✦</i> WEDDING EDIT <i>✦</i> READY TO SHIP <i>✦</i> WHATSAPP SHOPPING <i>✦</i> NEW ARRIVALS <i>✦</i> FESTIVE EDIT <i>✦</i></div></div>

<section class="section section-departments">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">SHOP THE WORLD OF TISHLA</span>
      <h2>Pieces for every kind<br>of occasion.</h2>
    </div>
    <a class="text-link" href="{{ route('shop') }}">View all departments <span>↗</span></a>
  </div>
  <div class="department-grid">
    @foreach($departments as $department)
      <a class="department-card reveal" href="{{ route('shop',['department'=>$department->slug]) }}">
        <div class="department-index">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</div>
        <div class="department-body"><strong>{{ $department->name }}</strong><small>Explore the edit</small></div>
        <span class="department-arrow">↗</span>
      </a>
    @endforeach
  </div>
</section>

<section class="editorial section">
  <div class="editorial-copy reveal">
    <span class="eyebrow">THE TISHLA POINT OF VIEW</span>
    <h2>Tradition, with<br><em>a sharper edge.</em></h2>
    <p>From fluid drapes to festive silhouettes, every edit balances Indian craft with a clean, modern attitude. Dress for the moment — then make it yours.</p>
    <a class="btn btn-outline" href="{{ route('page','about') }}">DISCOVER TISHLA <span>↗</span></a>
  </div>
  <div class="editorial-art reveal">
    <div class="editorial-panel"><span>CRAFT</span><strong>01</strong></div>
    <div class="editorial-panel"><span>FORM</span><strong>02</strong></div>
    <div class="editorial-panel"><span>DETAIL</span><strong>03</strong></div>
    <div class="editorial-orbit"><span>T</span></div>
  </div>
</section>

<section class="section section-featured">
  <div class="section-head reveal">
    <div>
      <span class="eyebrow">FEATURED NOW</span>
      <h2>A fresh take on Indian dressing.</h2>
    </div>
    <a class="text-link" href="{{ route('shop') }}">Shop all <span>↗</span></a>
  </div>
  <div class="product-grid">
    @foreach($featured as $product)
      <a class="product-card reveal" href="{{ route('product',$product->slug) }}">
        <div class="product-image">
          @if($product->images->first())
            <img src="{{ $product->images->first()->public_url }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy">
          @else
            <div class="image-fallback"><span>T</span></div>
          @endif
          <div class="product-overlay"><span>VIEW PIECE</span><b>↗</b></div>
          @if($product->product_badge)<span class="badge">{{ $product->product_badge }}</span>@endif
        </div>
        <div class="product-meta">
          <span>{{ $product->department?->name }}</span>
          <strong>{{ $product->name }}</strong>
          <b>₹{{ number_format((float)$product->base_price,0) }}</b>
        </div>
      </a>
    @endforeach
  </div>
</section>

<section class="community-feature">
  <div class="community-image reveal"><img src="{{ asset('assets/tishla-community.png') }}" alt="Join the Tishla WhatsApp community" loading="lazy"></div>
  <div class="community-copy reveal">
    <span class="eyebrow">STAY CLOSE TO TISHLA</span>
    <h2>New sarees.<br><em>New stories.</em></h2>
    <p>Receive daily saree and dress updates, new arrivals, offers and collection drops through our WhatsApp community.</p>
    <a class="btn btn-dark" href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">CHAT ON WHATSAPP <span>↗</span></a>
    <small>Personal assistance · Product enquiries · New arrivals</small>
  </div>
</section>

<section class="service-strip">
  <div><span>01</span><strong>CURATED IN SURAT</strong><small>A local eye for modern Indian dressing.</small></div>
  <div><span>02</span><strong>WHATSAPP SHOPPING</strong><small>Easy personal assistance when you need it.</small></div>
  <div><span>03</span><strong>INDIAWIDE DELIVERY</strong><small>Designed to arrive beautifully.</small></div>
  <div><span>04</span><strong>READY FOR THE OCCASION</strong><small>Wedding, festive, party and beyond.</small></div>
</section>

<section class="quote-band">
  <span>“</span>
  <h2>Made close to the craft.<br>Worn with a modern point of view.</h2>
  <small>TISHLA BY PURNIKA SALES · SURAT</small>
</section>
@endsection