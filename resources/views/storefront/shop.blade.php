@extends('layouts.storefront')

@section('content')
<section class="listing-hero">
  <div>
    <span class="eyebrow">THE TISHLA SHOP</span>
    <h1>Find your<br><em>next favourite.</em></h1>
    <p>Shop sarees, lehengas, sets and occasionwear through curated edits, silhouettes and price points.</p>
  </div>
  <div class="listing-hero-art"><span>T</span><small>CURATED<br>IN SURAT</small></div>
</section>

<div class="collection-strip">
  <a href="{{ route('shop', request()->except(['collection','page'])) }}" class="{{ empty($filters['collection']) ? 'active':'' }}">ALL</a>
  @foreach($collections as $collection)
    <a href="{{ route('shop', array_merge(request()->except('page'),['collection'=>$collection->slug])) }}" class="{{ $filters['collection']===$collection->slug ? 'active':'' }}">{{ strtoupper($collection->name) }}</a>
  @endforeach
</div>

<section class="shop-shell">
  <aside class="shop-filters" data-filter-panel>
    <div class="filter-mobile-head"><strong>FILTERS</strong><button type="button" data-filter-close>×</button></div>
    <div class="filter-heading"><span class="eyebrow">REFINE</span><a href="{{ route('shop') }}">RESET</a></div>

    <div class="filter-group">
      <span class="filter-title">DEPARTMENT</span>
      <a href="{{ route('shop', array_filter(['q'=>$filters['q'],'collection'=>$filters['collection'],'product_type'=>$filters['product_type'],'sort'=>$filters['sort'],'min_price'=>$filters['min_price'],'max_price'=>$filters['max_price']])) }}" class="{{ empty($filters['department']) ? 'active':'' }}">All departments</a>
      @foreach($departments as $department)
        <a href="{{ route('shop', array_filter(['q'=>$filters['q'],'department'=>$department->slug,'collection'=>$filters['collection'],'product_type'=>$filters['product_type'],'sort'=>$filters['sort'],'min_price'=>$filters['min_price'],'max_price'=>$filters['max_price']])) }}" class="{{ $filters['department']===$department->slug ? 'active':'' }}">{{ $department->name }} <span>→</span></a>
      @endforeach
    </div>

    <div class="filter-group">
      <span class="filter-title">PRODUCT TYPE</span>
      @foreach($productTypes as $type)
        <a href="{{ route('shop', array_filter(['q'=>$filters['q'],'department'=>$filters['department'],'collection'=>$filters['collection'],'product_type'=>$type->slug,'sort'=>$filters['sort'],'min_price'=>$filters['min_price'],'max_price'=>$filters['max_price']])) }}" class="{{ $filters['product_type']===$type->slug ? 'active':'' }}">{{ $type->name }} <span>→</span></a>
      @endforeach
    </div>

    <form class="filter-group price-filter" method="get">
      @foreach(['q','department','collection','product_type','sort'] as $key)
        @if($filters[$key])<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif
      @endforeach
      <span class="filter-title">PRICE RANGE</span>
      <div class="price-fields"><input type="number" name="min_price" value="{{ $filters['min_price'] }}" min="0" placeholder="₹ Min"><span>—</span><input type="number" name="max_price" value="{{ $filters['max_price'] }}" min="0" placeholder="₹ Max"></div>
      <button class="btn btn-dark wide" type="submit">APPLY RANGE</button>
    </form>

    <div class="filter-help"><span>✦</span><strong>Need help choosing?</strong><p>Tell us the occasion, colour, size or budget and we'll help you narrow it down.</p><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">ASK TISHLA ↗</a></div>
  </aside>

  <div class="shop-content">
    <div class="shop-toolbar">
      <div class="shop-count"><span class="eyebrow">YOUR EDIT</span><strong>{{ $products->total() }} <em>pieces</em></strong></div>
      <div class="toolbar-actions">
        <button type="button" class="mobile-filter-button" data-filter-open>FILTERS <span>＋</span></button>
        <form method="get" class="inline-search">
          @foreach(['department','collection','product_type','min_price','max_price'] as $key)
            @if($filters[$key])<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif
          @endforeach
          <input name="q" value="{{ $filters['q'] }}" placeholder="Search by saree, silk, organza…">
          <button type="submit" aria-label="Search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4.2 4.2"></path></svg></button>
        </form>
        <form method="get" class="sort-box">
          @foreach(['q','department','collection','product_type','min_price','max_price'] as $key)
            @if($filters[$key])<input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">@endif
          @endforeach
          <label>SORT <select name="sort" onchange="this.form.submit()"><option value="newest" {{ $filters['sort']==='newest'?'selected':'' }}>Newest</option><option value="price_asc" {{ $filters['sort']==='price_asc'?'selected':'' }}>Price low to high</option><option value="price_desc" {{ $filters['sort']==='price_desc'?'selected':'' }}>Price high to low</option><option value="name" {{ $filters['sort']==='name'?'selected':'' }}>Name A–Z</option></select></label>
        </form>
      </div>
    </div>

    <div class="active-filters">
      @if($filters['q'])<span>“{{ $filters['q'] }}” <a href="{{ route('shop',request()->except('q','page')) }}">×</a></span>@endif
      @if($filters['department'])<span>{{ ucfirst(str_replace('-',' ',$filters['department'])) }} <a href="{{ route('shop',request()->except('department','page')) }}">×</a></span>@endif
      @if($filters['product_type'])<span>{{ str_replace('-',' ',ucfirst($filters['product_type'])) }} <a href="{{ route('shop',request()->except('product_type','page')) }}">×</a></span>@endif
      @if($filters['collection'])<span>{{ str_replace('-',' ',ucfirst($filters['collection'])) }} <a href="{{ route('shop',request()->except('collection','page')) }}">×</a></span>@endif
      @if($filters['min_price'] || $filters['max_price'])<span>₹{{ $filters['min_price'] ?: '0' }} — ₹{{ $filters['max_price'] ?: '∞' }} <a href="{{ route('shop',request()->except(['min_price','max_price','page'])) }}">×</a></span>@endif
    </div>

    <div class="product-grid shop-grid">
      @forelse($products as $product)
        @php $isSaved=$wishlist->contains((string)$product->id); $defaultVariant=$product->variants->first(); $price=(float)($defaultVariant?->price ?: $product->base_price); $compare=(float)($defaultVariant?->compare_at_price ?: 0); $image=$product->images->first(); @endphp
        <article class="shop-product-card reveal">
          <div class="shop-product-image">
            <a href="{{ route('product',$product->slug) }}" class="product-image-link">
              @if($image)<img src="{{ $image->public_url }}" alt="{{ $image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-fallback"><span>T</span></div>@endif
              @if($product->product_badge)<span class="card-badge">{{ $product->product_badge }}</span>@endif
              <span class="view-pill">VIEW DETAILS <b>↗</b></span>
            </a>
            <form method="post" action="{{ route('wishlist.toggle',$product) }}" class="wishlist-form">@csrf<button type="submit" class="wishlist-button {{ $isSaved?'saved':'' }}" aria-label="{{ $isSaved?'Remove from wishlist':'Save to wishlist' }}">{{ $isSaved?'♥':'♡' }}</button></form>
          </div>
          <div class="shop-product-meta">
            <span>{{ $product->department?->name }} · {{ $product->product_type ?: 'TISHLA' }}</span>
            <a href="{{ route('product',$product->slug) }}"><strong>{{ $product->name }}</strong></a>
            <div class="price-line"><b>₹{{ number_format($price,0) }}</b>@if($compare>$price)<del>₹{{ number_format($compare,0) }}</del>@endif</div>
            @if($product->variants->count()>1)<small>{{ $product->variants->count() }} options available</small>@elseif($defaultVariant)<small>Ready to shop · {{ $defaultVariant->sku }}</small>@endif
          </div>
          <form method="post" action="{{ route('cart.add',$product) }}" class="quick-add-form">
            @csrf
            @if($defaultVariant)<input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">@endif
            <input type="hidden" name="quantity" value="1">
            <button type="submit">ADD TO BAG <span>+</span></button>
          </form>
        </article>
      @empty
        <div class="empty-state large"><div class="empty-icon">⌁</div><strong>No pieces match this edit.</strong><span>Try removing a filter or search for another fabric, department or price range.</span><a class="btn btn-dark" href="{{ route('shop') }}">RESET SHOP</a></div>
      @endforelse
    </div>

    <div class="pagination-wrap">{{ $products->links() }}</div>
  </div>
</section>
@endsection
