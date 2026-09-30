@extends('layouts.storefront')

@section('content')
<section class="page-hero shop-hero">
  <div>
    <span class="eyebrow">THE TISHLA SHOP</span>
    <h1>Find your<br><em>next occasion.</em></h1>
    <p>Explore sarees, lehengas, sets and silhouettes through a more considered shopping experience.</p>
  </div>
  <div class="shop-hero-mark"><span>⌁</span><small>CURATED<br>IN SURAT</small></div>
</section>

<div class="collection-rail">
  <a href="{{ route('shop',request()->except(['collection','page'])) }}" class="{{ empty($filters['collection']) ? 'active' : '' }}">ALL EDITS</a>
  @foreach($collections as $collection)
    <a href="{{ route('shop',array_merge(request()->except('page'),['collection'=>$collection->slug])) }}" class="{{ $filters['collection']===$collection->slug ? 'active' : '' }}">{{ strtoupper($collection->name) }}</a>
  @endforeach
</div>

<section class="listing premium-listing">
  <aside class="filters">
    <div class="filter-heading"><span class="eyebrow">REFINE</span><a href="{{ route('shop') }}">RESET</a></div>

    <div class="filter-block">
      <span class="filter-title">DEPARTMENTS</span>
      <a href="{{ route('shop',array_filter(['q'=>$filters['q'],'collection'=>$filters['collection'],'sort'=>$filters['sort'],'min_price'=>$filters['min_price'],'max_price'=>$filters['max_price']])) }}" class="{{ empty($filters['department']) ? 'active' : '' }}">All</a>
      @foreach($departments as $department)
        <a href="{{ route('shop',array_filter(['q'=>$filters['q'],'department'=>$department->slug,'collection'=>$filters['collection'],'sort'=>$filters['sort'],'min_price'=>$filters['min_price'],'max_price'=>$filters['max_price']])) }}" class="{{ $filters['department']===$department->slug ? 'active' : '' }}">
          <span>{{ $department->name }}</span><small>{{ $products->total() && $filters['department']===$department->slug ? $products->total() : '' }}</small>
        </a>
      @endforeach
    </div>

    <form class="filter-block price-filter" method="get">
      @if($filters['q'])<input type="hidden" name="q" value="{{ $filters['q'] }}">@endif
      @if($filters['department'])<input type="hidden" name="department" value="{{ $filters['department'] }}">@endif
      @if($filters['collection'])<input type="hidden" name="collection" value="{{ $filters['collection'] }}">@endif
      @if($filters['sort'])<input type="hidden" name="sort" value="{{ $filters['sort'] }}">@endif
      <span class="filter-title">PRICE RANGE</span>
      <div class="price-fields"><input type="number" name="min_price" value="{{ $filters['min_price'] }}" placeholder="Min"><span>—</span><input type="number" name="max_price" value="{{ $filters['max_price'] }}" placeholder="Max"></div>
      <button class="btn btn-light wide" type="submit">APPLY PRICE</button>
    </form>

    <div class="filter-note">
      <span>✦</span>
      <p>Need help choosing? Our team can guide you personally on WhatsApp.</p>
      <a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">ASK OUR TEAM ↗</a>
    </div>
  </aside>

  <div class="shop-results">
    <div class="listing-top shop-toolbar">
      <div>
        <span class="eyebrow">THE COLLECTION</span>
        <strong>{{ $products->total() }} <em>pieces</em></strong>
      </div>
      <form class="shop-search" method="get">
        @foreach(['department','collection','min_price','max_price'] as $key)
          @if($filters[$key])<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif
        @endforeach
        <input name="q" value="{{ $filters['q'] }}" placeholder="Search saree, silk, organza…">
        <button class="search-submit" aria-label="Search">⌕</button>
      </form>
      <form class="sort-field" method="get">
        @foreach(['q','department','collection','min_price','max_price'] as $key)
          @if($filters[$key])<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif
        @endforeach
        <span>SORT</span>
        <select name="sort" onchange="this.form.submit()">
          <option value="newest" {{ $filters['sort']==='newest'?'selected':'' }}>Newest</option>
          <option value="price_asc" {{ $filters['sort']==='price_asc'?'selected':'' }}>Price: Low to High</option>
          <option value="price_desc" {{ $filters['sort']==='price_desc'?'selected':'' }}>Price: High to Low</option>
          <option value="name" {{ $filters['sort']==='name'?'selected':'' }}>Name</option>
        </select>
      </form>
    </div>

    <div class="active-filters">
      @if($filters['q'])<span>“{{ $filters['q'] }}” <a href="{{ route('shop',request()->except('q','page')) }}">×</a></span>@endif
      @if($filters['department'])<span>{{ ucfirst(str_replace('-',' ',$filters['department'])) }} <a href="{{ route('shop',request()->except('department','page')) }}">×</a></span>@endif
      @if($filters['collection'])<span>{{ ucfirst(str_replace('-',' ',$filters['collection'])) }} <a href="{{ route('shop',request()->except('collection','page')) }}">×</a></span>@endif
      @if($filters['min_price'] || $filters['max_price'])<span>₹{{ $filters['min_price'] ?: '0' }} — ₹{{ $filters['max_price'] ?: '∞' }} <a href="{{ route('shop',request()->except(['min_price','max_price','page'])) }}">×</a></span>@endif
    </div>

    <div class="product-grid shop-grid">
      @forelse($products as $product)
        @php $isSaved=$wishlist->contains((string)$product->id); $defaultVariant=$product->variants->first(); @endphp
        <article class="product-card product-card-pro reveal">
          <div class="product-image">
            <a href="{{ route('product',$product->slug) }}" class="product-image-link">
              @if($product->images->first())
                <img src="{{ $product->images->first()->public_url }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy">
              @else
                <div class="image-fallback"><span>T</span></div>
              @endif
              @if($product->product_badge)<span class="badge">{{ $product->product_badge }}</span>@endif
              <span class="quick-view">QUICK VIEW ↗</span>
            </a>
            <form method="post" action="{{ route('wishlist.toggle',$product) }}" class="wishlist-form">@csrf
              <button type="submit" class="wishlist-button {{ $isSaved ? 'saved' : '' }}" aria-label="{{ $isSaved ? 'Remove from wishlist' : 'Save to wishlist' }}">{{ $isSaved ? '♥' : '♡' }}</button>
            </form>
          </div>
          <div class="product-meta">
            <span>{{ $product->department?->name }}</span>
            <a href="{{ route('product',$product->slug) }}"><strong>{{ $product->name }}</strong></a>
            <div class="product-price-row"><b>₹{{ number_format((float)$product->base_price,0) }}</b>@if($product->variants->count()>1)<small>{{ $product->variants->count() }} options</small>@elseif($product->variants->count()===1)<small>Ready to shop</small>@endif</div>
          </div>
          <form method="post" action="{{ route('cart.add',$product) }}" class="quick-add-form">
            @csrf
            @if($defaultVariant)<input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">@endif
            <input type="hidden" name="quantity" value="1">
            <button type="submit">ADD TO BAG <span>+</span></button>
          </form>
        </article>
      @empty
        <div class="empty large"><strong>Nothing matched this edit.</strong><span>Try another collection, price range or search.</span><a class="btn btn-dark" href="{{ route('shop') }}">RESET THE SHOP</a></div>
      @endforelse
    </div>

    <div class="pagination-wrap">{{ $products->links() }}</div>
  </div>
</section>
@endsection