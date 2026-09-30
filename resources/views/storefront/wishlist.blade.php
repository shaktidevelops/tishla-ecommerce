@extends('layouts.storefront')

@section('content')
<section class="inner-hero">
  <div><span class="eyebrow">YOUR SAVED EDIT</span><h1>Pieces to<br><em>come back to.</em></h1><p>Save favourites while you browse. Your edit stays in this browser until you remove a piece.</p></div>
  <div class="inner-hero-mark">♡</div>
</section>

<section class="home-section wishlist-section">
  @if($products->count())
    <div class="section-heading-row"><div><span class="eyebrow">WISHLIST</span><h2>{{ $products->count() }} {{ $products->count()===1?'piece':'pieces' }} saved.</h2></div><a class="text-link" href="{{ route('shop') }}">CONTINUE SHOPPING <span>↗</span></a></div>
    <div class="product-grid wishlist-grid">
      @foreach($products as $product)
        @php $image=$product->images->first(); $variant=$product->variants->first(); @endphp
        <article class="shop-product-card reveal">
          <div class="shop-product-image"><a href="{{ route('product',$product->slug) }}" class="product-image-link">@if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif @if($product->product_badge)<span class="card-badge">{{ $product->product_badge }}</span>@endif</a></div>
          <div class="shop-product-meta"><span>{{ $product->department?->name }}</span><a href="{{ route('product',$product->slug) }}"><strong>{{ $product->name }}</strong></a><div class="price-line"><b>₹{{ number_format((float)($variant?->price ?: $product->base_price),0) }}</b></div></div>
          <div class="wishlist-actions"><a class="btn btn-dark" href="{{ route('product',$product->slug) }}">VIEW PIECE</a><form method="post" action="{{ route('wishlist.toggle',$product) }}">@csrf<button type="submit" class="remove-save">REMOVE ×</button></form></div>
        </article>
      @endforeach
    </div>
  @else
    <div class="wishlist-empty"><div class="empty-icon">♡</div><span class="eyebrow">YOUR EDIT IS WAITING</span><h2>Save something<br><em>beautiful.</em></h2><p>Tap the heart on any piece you love and it will appear here.</p><a class="btn btn-dark" href="{{ route('shop') }}">EXPLORE THE SHOP <span>↗</span></a></div>
  @endif
</section>
@endsection
