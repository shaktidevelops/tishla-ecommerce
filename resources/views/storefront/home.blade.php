@extends('layouts.storefront')

@section('content')
<section class="hero">
  <div class="hero-copy reveal">
    <div class="hero-kicker"><span class="eyebrow">THE TISHLA EDIT</span><span class="hero-line"></span><span>SS26</span></div>
    <h1>Indian craft.<br><em>Contemporary spirit.</em></h1>
    <p>Statement sarees, celebration-ready silhouettes and polished everyday pieces — curated from Surat for the way you dress now.</p>
    <div class="hero-actions">
      <a class="btn btn-dark" href="{{ route('shop') }}">SHOP THE EDIT <span>↗</span></a>
      <a class="btn btn-light" href="{{ route('shop',['department'=>'sarees']) }}">EXPLORE SAREES</a>
    </div>
    <div class="hero-signature">
      <span>Designed in Surat</span>
      <span>Made to be remembered</span>
    </div>
  </div>

  <div class="hero-art reveal">
    @if($featured->first()?->images->first())
      <img src="{{ $featured->first()->images->first()->public_url }}" alt="{{ $featured->first()->name }}">
    @else
      <div class="hero-art-fallback"><span>T</span></div>
    @endif
    <div class="hero-frame"></div>
    <div class="hero-label hero-label-top">TISHLA / 01</div>
    <div class="hero-label hero-label-bottom">SURAT · INDIA</div>
    <div class="hero-stamp"><span>T</span><small>PURNIKA SALES</small></div>
  </div>
</section>

<div class="marquee"><div>NEW ARRIVALS <i>✦</i> FESTIVE EDIT <i>✦</i> WEDDING EDIT <i>✦</i> READY TO SHIP <i>✦</i> NEW ARRIVALS <i>✦</i> FESTIVE EDIT <i>✦</i></div></div>

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