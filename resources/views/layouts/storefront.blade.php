<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Tishla by Purnika Sales — Indian fashion from Surat.' }}">
<link rel="icon" type="image/png" href="{{ asset('tishla-favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
<link rel="stylesheet" href="{{ asset('assets/concierge.css') }}">
</head>
<body>
<div class="announcement"><div>COMPLIMENTARY SHIPPING ACROSS INDIA</div><span>•</span><div>WHATSAPP SHOPPING AVAILABLE</div><span>•</span><div>CURATED IN SURAT</div></div>

<header class="site-header">
  <div class="nav-wrap">
    <div class="nav-top">
      <button class="mobile-toggle" data-menu-toggle aria-label="Open menu" aria-expanded="false">☰</button>
      <div class="nav-spacer"></div>
      <a class="brand-logo" href="{{ route('home') }}" aria-label="Tishla by Purnika Sales"><img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla by Purnika Sales"></a>
      <div class="nav-actions" aria-label="Store actions">
        <a href="{{ route('shop') }}" aria-label="Search">⌕</a>
        <a href="{{ route('wishlist') }}" class="wishlist-nav-link" aria-label="Wishlist">♡ <span class="nav-count">{{ count(session('wishlist',[])) }}</span></a>
        <a href="{{ route('cart') }}" aria-label="Shopping bag">Bag <span class="bag-count">{{ collect(session('cart',[]))->sum('quantity') }}</span></a>
        @auth<a class="admin-link" href="{{ route('admin.dashboard') }}">ADMIN</a>@endauth
      </div>
    </div>
    <nav class="main-nav" data-nav>
      <a href="{{ route('shop') }}">SHOP</a><a href="{{ route('shop',['department'=>'sarees']) }}">SAREES</a><a href="{{ route('shop',['department'=>'lehengas']) }}">LEHENGAS</a><a href="{{ route('shop',['department'=>'kurtis-sets']) }}">KURTIS &amp; SETS</a><a href="{{ route('shop',['department'=>'suits']) }}">SUITS</a><a href="{{ route('page','about') }}">ABOUT</a><a href="{{ route('contact') }}">CONTACT</a><a href="{{ route('track-order') }}">TRACK ORDER</a>
    </nav>
  </div>
</header>

@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
@if($errors->any() && !request()->routeIs('contact'))<div class="flash error">{{ $errors->first() }}</div>@endif

<main>@yield('content')</main>

<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-brand">
      <img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla by Purnika Sales" class="footer-logo">
      <p>Indian occasionwear and contemporary silhouettes, curated with a Surat point of view.</p>
      <div class="footer-socials" aria-label="Tishla social media">
        <a class="social-orbit" href="https://www.instagram.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Instagram @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4.2" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.3" cy="6.8" r="1.15" fill="currentColor"/></svg></a>
        <a class="social-orbit" href="https://www.facebook.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Facebook @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.2 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.7-.1-1.5-.2-2.4-.2-2.4 0-4 1.5-4 4.1v2.4H8.4V13h2.7v8z" fill="currentColor"/></svg></a>
        <a class="social-orbit" href="https://www.youtube.com/@tishlawear" target="_blank" rel="noopener noreferrer" aria-label="YouTube @tishlawear"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.4 7.2a2.8 2.8 0 0 0-2-2C17.6 4.7 12 4.7 12 4.7s-5.6 0-7.4.5a2.8 2.8 0 0 0-2 2C2.1 9 2.1 12 2.1 12s0 3 .5 4.8a2.8 2.8 0 0 0 2 2c1.8.5 7.4.5 7.4.5s5.6 0 7.4-.5a2.8 2.8 0 0 0 2-2c.5-1.8.5-4.8.5-4.8s0-3-.5-4.8z" fill="currentColor"/><path d="M10 15.4V8.6l5.5 3.4z" fill="#24030C"/></svg></a>
      </div><span class="social-handle">@tishlawear · Instagram · Facebook · YouTube</span>
    </div>
    <div class="footer-column"><span class="eyebrow">SHOP</span><a href="{{ route('shop') }}">All Products</a><a href="{{ route('shop',['department'=>'sarees']) }}">Sarees</a><a href="{{ route('shop',['department'=>'lehengas']) }}">Lehengas</a><a href="{{ route('shop',['department'=>'kurtis-sets']) }}">Kurtis &amp; Sets</a><a href="{{ route('cart') }}">Shopping Bag</a></div>
    <div class="footer-column"><span class="eyebrow">SERVICE</span><a href="{{ route('track-order') }}">Track Order</a><a href="{{ route('page','shipping') }}">Shipping</a><a href="{{ route('page','returns') }}">Returns</a><a href="{{ route('page','size-guide') }}">Size Guide</a><a href="{{ route('page','faq') }}">FAQ</a><a href="{{ route('contact') }}">Contact Concierge</a></div>
    <div class="footer-column footer-contact"><span class="eyebrow">LET'S TALK</span><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a><a href="mailto:{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}" target="_blank" rel="noopener noreferrer">{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }} ↗</a><a href="https://www.instagram.com/tishlawear/" target="_blank" rel="noopener noreferrer">Instagram · @tishlawear ↗</a><a href="https://www.facebook.com/tishlawear/" target="_blank" rel="noopener noreferrer">Facebook · @tishlawear ↗</a><a href="https://www.youtube.com/@tishlawear" target="_blank" rel="noopener noreferrer">YouTube · @tishlawear ↗</a><span>Surat, Gujarat · India</span></div>
  </div>
  <div class="footer-note"><span>ALIVE IS AWESOME!</span><span>© {{ date('Y') }} Tishla by Purnika Sales</span><span>INDIAN FASHION · SURAT</span></div>
</footer>

<a class="floating-wa" href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with Tishla on WhatsApp"><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.1 3.9A9.9 9.9 0 0 0 4.4 16.1L3 21l5-1.3A9.9 9.9 0 1 0 20.1 3.9ZM12 19.1c-1.5 0-2.9-.4-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A7.9 7.9 0 1 1 12 19.1Zm4.4-5.8c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1-.2.3-.6.8-.7 1-.1.1-.3.2-.5.1-1.8-.9-3-1.6-4.1-3.7-.3-.5.3-.5.7-1.3.1-.2.1-.4 0-.5l-.4-1c-.1-.3-.3-.3-.5-.3h-.4c-.1 0-.4.1-.6.3-.6.6-.8 1.4-.8 2.2 0 .5.1 1 .3 1.4 0 .1 1.2 2.7 4.1 4.2 2.4 1.2 2.4.8 2.8.8.6 0 1.9-.8 2.1-1.6.1-.4.1-.7 0-.8Z" fill="currentColor"/></svg></span><em>WhatsApp</em></a>
<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>
