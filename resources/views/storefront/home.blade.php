@extends('layouts.storefront')

@section('content')
<section class="home-hero">
  <div class="home-hero-media">
    <img src="{{ asset('assets/tishla-banner.png') }}" alt="Tishla by Purnika Sales — Indian occasionwear" fetchpriority="high">
  </div>
  <div class="home-hero-overlay"></div>
  <div class="hero-grid">
    <div class="hero-copy reveal">
      <span class="eyebrow light">TISHLA BY PURNIKA SALES · SURAT</span>
      <h1>Dress the<br><em>moment.</em></h1>
      <p>Modern Indian occasionwear, thoughtfully selected for weddings, festive nights, celebrations and everything in between.</p>
      <div class="hero-actions">
        <a class="btn btn-light-solid" href="{{ route('shop',['collection'=>'new-arrivals']) }}">SHOP NEW IN <span>↗</span></a>
        <a class="hero-link" href="{{ route('shop',['department'=>'sarees']) }}">EXPLORE SAREES <span>↗</span></a>
      </div>
    </div>
    <div class="hero-aside reveal">
      <div class="hero-card">
        <span>THE 2026 EDIT</span>
        <strong>Indian silhouettes.<br>Fresh energy.</strong>
        <small>CURATED IN SURAT</small>
      </div>
      <div class="hero-scroll"><span></span>SCROLL TO EXPLORE</div>
    </div>
  </div>
</section>

<div class="marquee marquee-dark"><div>NEW IN <i>✦</i> WEDDING EDIT <i>✦</i> FESTIVE EDIT <i>✦</i> PARTY EDIT <i>✦</i> WHATSAPP SHOPPING <i>✦</i> READY TO SHIP <i>✦</i> NEW IN <i>✦</i> WEDDING EDIT <i>✦</i></div></div>

<section class="home-section intro-section">
  <div class="intro-mark reveal"><span>T</span><small>01 / 05</small></div>
  <div class="intro-copy reveal">
    <span class="eyebrow">THE TISHLA POINT OF VIEW</span>
    <h2>Indian, but<br><em>never expected.</em></h2>
    <p>From fluid drapes to polished festive sets, Tishla brings together occasionwear with a cleaner, more current point of view. The mood is celebratory. The styling is personal.</p>
    <a class="text-link" href="{{ route('page','about') }}">DISCOVER OUR STORY <span>↗</span></a>
  </div>
  <div class="intro-rule"><span></span></div>
</section>

<section class="home-section">
  <div class="section-heading-row reveal">
    <div><span class="eyebrow">SHOP BY SILHOUETTE</span><h2>Find your<br><em>signature.</em></h2></div>
    <a class="text-link" href="{{ route('shop') }}">VIEW ALL <span>↗</span></a>
  </div>
  <div class="silhouette-grid">
    @foreach($departments as $department)
      @php $highlight = $departmentHighlights->get($department->id); $image = $highlight?->images?->first(); @endphp
      <a class="silhouette-card reveal silhouette-{{ $loop->iteration }}" href="{{ route('shop',['department'=>$department->slug]) }}">
        @if($image)<img src="{{ $image->public_url }}" alt="{{ $department->name }}" loading="lazy">@endif
        <div class="silhouette-overlay"></div>
        <div class="silhouette-content">
          <span>{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }} · {{ $department->active_products_count }} PIECES</span>
          <strong>{{ $department->name }}</strong>
          <small>EXPLORE EDIT <b>↗</b></small>
        </div>
      </a>
    @endforeach
  </div>
</section>

<section class="home-section feature-section">
  <div class="section-heading-row reveal">
    <div><span class="eyebrow">EDITOR'S PICKS</span><h2>Pieces worth<br><em>pausing for.</em></h2></div>
    <a class="text-link" href="{{ route('shop') }}">SHOP ALL <span>↗</span></a>
  </div>
  @if($featured->count())
  <div class="feature-grid">
    @foreach($featured as $product)
      @php $image = $product->images->first(); $variant = $product->variants->first(); @endphp
      <a class="fashion-card reveal" href="{{ route('product',$product->slug) }}">
        <div class="fashion-card-image">
          @if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif
          @if($product->product_badge)<span class="card-badge">{{ $product->product_badge }}</span>@endif
          <span class="card-number">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span>
          <span class="card-view">VIEW PIECE <b>↗</b></span>
        </div>
        <div class="fashion-card-meta">
          <div><span>{{ $product->department?->name }}</span><strong>{{ $product->name }}</strong></div>
          <b>₹{{ number_format((float)($variant?->price ?: $product->base_price),0) }}</b>
        </div>
      </a>
    @endforeach
  </div>
  @else
    <div class="empty-state"><strong>Your featured edit is being prepared.</strong><a class="btn btn-dark" href="{{ route('shop') }}">EXPLORE SHOP</a></div>
  @endif
</section>

<section class="occasion-band">
  <div class="occasion-intro reveal"><span class="eyebrow light">SHOP BY MOOD</span><h2>For the nights<br><em>you'll remember.</em></h2><p>Choose an edit, then find the piece that feels like you.</p></div>
  <div class="occasion-grid">
    @php $occasionSlugs = ['wedding-edit','festive-edit','party-edit']; $occasionNames = ['Wedding Edit','Festive Edit','Party Edit']; @endphp
    @foreach($occasionSlugs as $index=>$slug)
      @php $occasionProduct = $newArrivals->get($index); $occasionImage = $occasionProduct?->images?->first(); @endphp
      <a class="occasion-card reveal" href="{{ route('shop',['collection'=>$slug]) }}">
        @if($occasionImage)<img src="{{ $occasionImage->public_url }}" alt="{{ $occasionNames[$index] }}" loading="lazy">@endif
        <div class="occasion-overlay"></div>
        <div><span>0{{ $index+1 }}</span><strong>{{ $occasionNames[$index] }}</strong><small>SHOP EDIT <b>↗</b></small></div>
      </a>
    @endforeach
  </div>
</section>

<section class="home-section new-in-section">
  <div class="section-heading-row reveal">
    <div><span class="eyebrow">JUST LANDED</span><h2>New in<br><em>Tishla.</em></h2></div>
    <a class="text-link" href="{{ route('shop',['collection'=>'new-arrivals']) }}">SEE EVERYTHING NEW <span>↗</span></a>
  </div>
  <div class="product-rail">
    @foreach($newArrivals as $product)
      @php $image = $product->images->first(); $variant = $product->variants->first(); $saved = collect(session('wishlist',[]))->contains((string)$product->id); @endphp
      <article class="rail-card reveal">
        <div class="rail-image">
          <a href="{{ route('product',$product->slug) }}">
            @if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif
          </a>
          <form method="post" action="{{ route('wishlist.toggle',$product) }}" class="wishlist-form">@csrf<button type="submit" class="wishlist-button {{ $saved ? 'saved':'' }}" aria-label="{{ $saved ? 'Remove from wishlist' : 'Save to wishlist' }}">{{ $saved ? '♥':'♡' }}</button></form>
        </div>
        <div class="rail-meta"><span>{{ $product->department?->name }}</span><a href="{{ route('product',$product->slug) }}"><strong>{{ $product->name }}</strong></a><b>₹{{ number_format((float)($variant?->price ?: $product->base_price),0) }}</b></div>
      </article>
    @endforeach
  </div>
</section>

<section class="community-block">
  <div class="community-image reveal"><img src="{{ asset('assets/tishla-community.png') }}" alt="Tishla WhatsApp community" loading="lazy"></div>
  <div class="community-copy reveal"><span class="eyebrow">A LITTLE CLOSER</span><h2>New drops,<br><em>straight to you.</em></h2><p>Join Tishla on WhatsApp for new sarees, dress updates, special offers, product enquiries and collection drops.</p><div class="community-note"><span>01</span> PERSONAL ASSISTANCE <span>02</span> NEW ARRIVALS <span>03</span> DIRECT ENQUIRIES</div><a class="btn btn-dark" href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">CHAT ON WHATSAPP <span>↗</span></a></div>
</section>

<section class="service-section">
  <div class="service-intro reveal"><span class="eyebrow">THE TISHLA DIFFERENCE</span><h2>The little details<br><em>matter.</em></h2></div>
  <div class="service-grid">
    <div class="service-card reveal"><span>01</span><strong>CURATED SELECTION</strong><p>Less noise. More pieces with a clear occasion and styling point of view.</p></div>
    <div class="service-card reveal"><span>02</span><strong>PERSONAL ASSISTANCE</strong><p>Need a second opinion on colour, silhouette, size or occasion? Message us.</p></div>
    <div class="service-card reveal"><span>03</span><strong>INDIAWIDE DELIVERY</strong><p>Order online and follow the journey through your Tishla order page.</p></div>
    <div class="service-card reveal"><span>04</span><strong>COD AVAILABLE</strong><p>Choose cash on delivery at checkout where available for your order.</p></div>
  </div>
</section>

@if($recentlyViewed->count())
<section class="home-section recent-section">
  <div class="section-heading-row reveal"><div><span class="eyebrow">WELCOME BACK</span><h2>Keep exploring.</h2></div><a class="text-link" href="{{ route('shop') }}">SHOP COLLECTION <span>↗</span></a></div>
  <div class="product-rail">
    @foreach($recentlyViewed as $product)
      @php $image = $product->images->first(); @endphp
      <a class="rail-card reveal" href="{{ route('product',$product->slug) }}"><div class="rail-image">@if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif</div><div class="rail-meta"><span>{{ $product->department?->name }}</span><strong>{{ $product->name }}</strong><b>₹{{ number_format((float)$product->base_price,0) }}</b></div></a>
    @endforeach
  </div>
</section>
@endif

<section class="quote-section">
  <div class="quote-mark">“</div>
  <p>Good clothes don't need to shout.<br>They make the moment feel like yours.</p>
  <span>TISHLA BY PURNIKA SALES · SURAT</span>
</section>
@endsection
