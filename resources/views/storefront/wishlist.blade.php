@extends('layouts.storefront')

@section('content')
<section class="page-hero wishlist-hero">
  <span class="eyebrow">YOUR SAVED EDIT</span>
  <h1>Pieces you<br><em>don't want to lose.</em></h1>
  <p>Keep favourites close while you browse. Your saved edit stays in this browser until you remove it.</p>
</section>

<section class="section wishlist-section">
  @if($products->count())
    <div class="section-head">
      <div><span class="eyebrow">WISHLIST</span><h2>{{ $products->count() }} {{ $products->count()===1?'piece':'pieces' }} saved.</h2></div>
      <a class="text-link" href="{{ route('shop') }}">Continue shopping <span>↗</span></a>
    </div>
    <div class="product-grid">
      @foreach($products as $product)
        <article class="product-card product-card-pro reveal">
          <a href="{{ route('product',$product->slug) }}" class="product-image">
            @if($product->images->first())
              <img src="{{ $product->images->first()->public_url }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy">
            @else
              <div class="image-fallback"><span>T</span></div>
            @endif
            @if($product->product_badge)<span class="badge">{{ $product->product_badge }}</span>@endif
          </a>
          <div class="product-meta">
            <span>{{ $product->department?->name }}</span>
            <a href="{{ route('product',$product->slug) }}"><strong>{{ $product->name }}</strong></a>
            <div class="product-price-row"><b>₹{{ number_format((float)$product->base_price,0) }}</b></div>
          </div>
          <div class="wishlist-actions">
            <a class="btn btn-light" href="{{ route('product',$product->slug) }}">VIEW PIECE</a>
            <form method="post" action="{{ route('wishlist.toggle',$product) }}">@csrf<button class="remove-save" type="submit">REMOVE ×</button></form>
          </div>
        </article>
      @endforeach
    </div>
  @else
    <div class="wishlist-empty">
      <div class="wishlist-emblem">♡</div>
      <span class="eyebrow">YOUR EDIT IS WAITING</span>
      <h2>Save something<br>beautiful.</h2>
      <p>Tap the heart on any piece you love and it will appear here.</p>
      <a class="btn btn-dark" href="{{ route('shop') }}">EXPLORE THE SHOP <span>↗</span></a>
    </div>
  @endif
</section>
@endsection