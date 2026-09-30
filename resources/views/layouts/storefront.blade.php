<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Tishla by Purnika Sales — contemporary Indian fashion from Surat.' }}">
<meta name="theme-color" content="#24121b">
<link rel="icon" type="image/png" href="{{ asset('tishla-favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:opsz,wght@6..96,400;6..96,500;6..96,600;6..96,700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
<link rel="stylesheet" href="{{ asset('assets/concierge.css') }}">
</head>
<body>
<div class="utility-bar">
  <div><span>CURATED IN SURAT</span><i>•</i><span>WHATSAPP SHOPPING</span><i>•</i><span>CASH ON DELIVERY</span></div>
  <a href="{{ route('track-order') }}">TRACK ORDER <b>↗</b></a>
</div>

<header class="site-header">
  <div class="nav-top">
    <div class="nav-left">
      <button class="mobile-toggle" data-menu-toggle aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
      <button class="icon-button search-open" data-search-open aria-label="Open search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4.2 4.2"></path></svg>
        <span class="action-label">Search</span>
      </button>
    </div>
    <a class="brand-logo" href="{{ route('home') }}" aria-label="Tishla by Purnika Sales">
      <img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla by Purnika Sales">
    </a>
    <div class="nav-actions">
      <a href="{{ route('wishlist') }}" class="header-action" aria-label="Wishlist">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.7c0 5.2-8.8 10-8.8 10s-8.8-4.8-8.8-10A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 8.8 2.7Z"></path></svg>
        <span class="action-label">Wishlist</span><b class="nav-count">{{ count(session('wishlist',[])) }}</b>
      </a>
      <a href="{{ route('cart') }}" class="header-action bag-action" aria-label="Shopping bag">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8.5h12l1 11H5l1-11Z"></path><path d="M9 9V6.8A3 3 0 0 1 12 4a3 3 0 0 1 3 2.8V9"></path></svg>
        <span class="action-label">Bag</span><b class="nav-count">{{ collect(session('cart',[]))->sum('quantity') }}</b>
      </a>
      @auth<a class="admin-link" href="{{ route('admin.dashboard') }}">ADMIN</a>@endauth
    </div>
  </div>

  <nav class="main-nav" data-nav>
    <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop','search') ? 'active' : '' }}">SHOP ALL</a>
    <a href="{{ route('shop',['collection'=>'new-arrivals']) }}">NEW IN</a>
    <a href="{{ route('shop',['department'=>'sarees']) }}">SAREES</a>
    <a href="{{ route('shop',['department'=>'lehengas']) }}">LEHENGAS</a>
    <a href="{{ route('shop',['department'=>'kurtis-sets']) }}">KURTIS &amp; SETS</a>
    <a href="{{ route('shop',['collection'=>'wedding-edit']) }}">WEDDING</a>
    <a href="{{ route('shop',['collection'=>'festive-edit']) }}">FESTIVE</a>
    <a href="{{ route('page','about') }}">ABOUT</a>
    <a href="{{ route('contact') }}">CONTACT</a>
  </nav>
</header>

<div class="search-panel" data-search-panel aria-hidden="true">
  <div class="search-panel-backdrop" data-search-close></div>
  <div class="search-panel-inner">
    <div class="search-panel-top"><span class="eyebrow">SEARCH TISHLA</span><button type="button" data-search-close aria-label="Close search">×</button></div>
    <form action="{{ route('search') }}" method="get" class="search-panel-form">
      <input type="search" name="q" placeholder="Search sarees, lehengas, silk, organza…" autocomplete="off" data-global-search>
      <button type="submit" aria-label="Search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4.2 4.2"></path></svg>
      </button>
    </form>
    <div class="search-suggestions">
      <span>Trending</span>
      <a href="{{ route('shop',['collection'=>'new-arrivals']) }}">New arrivals</a>
      <a href="{{ route('shop',['department'=>'sarees']) }}">Sarees</a>
      <a href="{{ route('shop',['department'=>'lehengas']) }}">Lehengas</a>
      <a href="{{ route('shop',['collection'=>'wedding-edit']) }}">Wedding edit</a>
    </div>
  </div>
</div>

@if(session('success'))<div class="flash success" role="status">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error" role="alert">{{ session('error') }}</div>@endif
@if($errors->any() && !request()->routeIs('contact'))<div class="flash error" role="alert">{{ $errors->first() }}</div>@endif

<main id="main-content">@yield('content')</main>

<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand">
      <div class="footer-brand-lockup"><img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla by Purnika Sales"></div>
      <p>Contemporary Indian occasionwear from Surat — made to feel special, easy to wear and unmistakably yours.</p>
      <a class="footer-whatsapp" href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer"><span>WhatsApp the studio</span><b>↗</b></a>
      <div class="footer-socials" aria-label="Tishla social media">
        <a class="social-orbit" href="https://www.instagram.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Instagram @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4.2"></circle><circle cx="17.3" cy="6.8" r="1.15" class="fill"></circle></svg></a>
        <a class="social-orbit" href="https://www.facebook.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Facebook @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="fill" d="M14.2 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.7-.1-1.5-.2-2.4-.2-2.4 0-4 1.5-4 4.1v2.4H8.4V13h2.7v8z"></path></svg></a>
        <a class="social-orbit" href="https://www.youtube.com/@tishlawear" target="_blank" rel="noopener noreferrer" aria-label="YouTube @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="fill" d="M21.4 7.2a2.8 2.8 0 0 0-2-2C17.6 4.7 12 4.7 12 4.7s-5.6 0-7.4.5a2.8 2.8 0 0 0-2 2C2.1 9 2.1 12 2.1 12s0 3 .5 4.8a2.8 2.8 0 0 0 2 2c1.8.5 7.4.5 7.4.5s5.6 0 7.4-.5a2.8 2.8 0 0 0 2-2c.5-1.8-.5-4.8-.5-4.8s0-3-.5-4.8z"></path><path d="m10 15.4 5.5-3.4L10 8.6v6.8Z" fill="#24121b"></path></svg></a>
      </div>
      <span class="social-handle">@tishlawear</span>
    </div>
    <div class="footer-column"><span class="eyebrow">SHOP</span><a href="{{ route('shop') }}">Shop all</a><a href="{{ route('shop',['collection'=>'new-arrivals']) }}">New in</a><a href="{{ route('shop',['department'=>'sarees']) }}">Sarees</a><a href="{{ route('shop',['department'=>'lehengas']) }}">Lehengas</a><a href="{{ route('shop',['department'=>'kurtis-sets']) }}">Kurtis &amp; Sets</a></div>
    <div class="footer-column"><span class="eyebrow">EDITS</span><a href="{{ route('shop',['collection'=>'wedding-edit']) }}">Wedding edit</a><a href="{{ route('shop',['collection'=>'festive-edit']) }}">Festive edit</a><a href="{{ route('shop',['collection'=>'party-edit']) }}">Party edit</a><a href="{{ route('shop',['collection'=>'ready-to-ship']) }}">Ready to ship</a></div>
    <div class="footer-column"><span class="eyebrow">SERVICE</span><a href="{{ route('track-order') }}">Track order</a><a href="{{ route('page','shipping') }}">Shipping</a><a href="{{ route('page','returns') }}">Returns</a><a href="{{ route('page','size-guide') }}">Size guide</a><a href="{{ route('page','faq') }}">FAQ</a><a href="{{ route('contact') }}">Contact</a></div>
    <div class="footer-column footer-contact"><span class="eyebrow">CONTACT</span><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">+91 {{ substr(env('TISHLA_WHATSAPP','919574716712'),2) }} ↗</a><a href="mailto:{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}">{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }} ↗</a><span>Surat, Gujarat · India</span><small>GST {{ number_format((float)env('TISHLA_GST_RATE','5'),0) }}% · Retail &amp; wholesale enquiries welcome</small></div>
  </div>
  <div class="footer-bottom"><span>ALIVE IS AWESOME!</span><span>© {{ date('Y') }} Tishla by Purnika Sales</span><span>INDIAN FASHION · SURAT</span></div>
</footer>

<a class="floating-wa" href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with Tishla on WhatsApp">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.1 3.9A9.9 9.9 0 0 0 4.4 16.1L3 21l5-1.3A9.9 9.9 0 1 0 20.1 3.9ZM12 19.1c-1.5 0-2.9-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A7.9 7.9 0 1 1 12 19.1Z" fill="none" stroke="currentColor" stroke-width="1.6"></path><path d="M16.4 13.3c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1-.2.3-.6.8-.7 1-.1.1-.3.2-.5.1-1.8-.9-3-1.6-4.1-3.7-.3-.5.3-.5.7-1.3.1-.2.1-.4 0-.5l-.4-1c-.1-.3-.3-.3-.5-.3h-.4c-.1 0-.4.1-.6.3-.6.6-.8 1.4-.8 2.2 0 .5.1 1 .3 1.4 0 .1 1.2 2.7 4.1 4.2 2.4 1.2 2.4.8 2.8.8.6 0 1.9-.8 2.1-1.6.1-.4.1-.7 0-.8Z" fill="currentColor"></path></svg><span>WhatsApp</span>
</a>

<nav class="mobile-bottom-nav" aria-label="Quick navigation">
  <a href="{{ route('home') }}"><span>⌂</span><small>Home</small></a>
  <a href="{{ route('shop') }}"><span>◌</span><small>Shop</small></a>
  <button type="button" data-search-open><span>⌕</span><small>Search</small></button>
  <a href="{{ route('wishlist') }}"><span>♡</span><small>Saved</small></a>
  <a href="{{ route('cart') }}"><span>▢</span><small>Bag · {{ collect(session('cart',[]))->sum('quantity') }}</small></a>
</nav>
<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>
