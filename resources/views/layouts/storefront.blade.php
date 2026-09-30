<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Tishla by Purnika Sales — Indian fashion from Surat.' }}">
<link rel="icon" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body>
<div class="announcement"><div>COMPLIMENTARY SHIPPING ACROSS INDIA</div><span>•</span><div>WHATSAPP SHOPPING AVAILABLE</div><span>•</span><div>CURATED IN SURAT</div></div>

<header class="site-header">
  <div class="nav-wrap">
    <button class="mobile-toggle" data-menu-toggle aria-label="Open menu" aria-expanded="false">☰</button>
    <a class="brand-logo" href="{{ route('home') }}" aria-label="Tishla by Purnika Sales">
      <img src="{{ asset('assets/tishla-logo.svg') }}" alt="Tishla by Purnika Sales">
    </a>
    <nav class="main-nav" data-nav>
      <a href="{{ route('shop') }}">SHOP</a>
      <a href="{{ route('shop',['department'=>'sarees']) }}">SAREES</a>
      <a href="{{ route('shop',['department'=>'lehengas']) }}">LEHENGAS</a>
      <a href="{{ route('shop',['department'=>'kurtis-sets']) }}">KURTIS &amp; SETS</a>
      <a href="{{ route('shop',['department'=>'suits']) }}">SUITS</a>
      <a href="{{ route('page','about') }}">ABOUT</a>
      <a href="{{ route('page','contact') }}">CONTACT</a>
    </nav>
    <div class="nav-actions" aria-label="Store actions">
      <a href="{{ route('shop') }}" aria-label="Search">⌕</a>
      <a href="{{ route('cart') }}" aria-label="Shopping bag">Bag <span class="bag-count">{{ collect(session('cart',[]))->sum('quantity') }}</span></a>
      @auth<a class="admin-link" href="{{ route('admin.dashboard') }}">ADMIN</a>@endauth
    </div>
  </div>
</header>

@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif

<main>@yield('content')</main>

<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-brand">
      <img src="{{ asset('assets/tishla-logo.svg') }}" alt="Tishla by Purnika Sales" class="footer-logo">
      <p>Indian occasionwear and contemporary silhouettes, curated with a Surat point of view.</p>
      <div class="footer-socials">
        <a href="https://www.instagram.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">IG</a>
        <a href="https://www.facebook.com/tishlawear/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">FB</a>
      </div>
    </div>

    <div class="footer-column">
      <span class="eyebrow">SHOP</span>
      <a href="{{ route('shop') }}">All Products</a>
      <a href="{{ route('shop',['department'=>'sarees']) }}">Sarees</a>
      <a href="{{ route('shop',['department'=>'lehengas']) }}">Lehengas</a>
      <a href="{{ route('shop',['department'=>'kurtis-sets']) }}">Kurtis &amp; Sets</a>
      <a href="{{ route('cart') }}">Shopping Bag</a>
    </div>

    <div class="footer-column">
      <span class="eyebrow">SERVICE</span>
      <a href="{{ route('page','shipping') }}">Shipping</a>
      <a href="{{ route('page','returns') }}">Returns</a>
      <a href="{{ route('page','size-guide') }}">Size Guide</a>
      <a href="{{ route('page','faq') }}">FAQ</a>
      <a href="{{ route('page','contact') }}">Contact</a>
    </div>

    <div class="footer-column footer-contact">
      <span class="eyebrow">LET'S TALK</span>
      <a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
      <a href="mailto:{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}" target="_blank" rel="noopener noreferrer">{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }} ↗</a>
      <a href="https://www.instagram.com/tishlawear/" target="_blank" rel="noopener noreferrer">Instagram ↗</a>
      <a href="https://www.facebook.com/tishlawear/" target="_blank" rel="noopener noreferrer">Facebook ↗</a>
      <span>Surat, Gujarat · India</span>
    </div>
  </div>

  <div class="footer-note">
    <span>ALIVE IS AWESOME!</span>
    <span>© {{ date('Y') }} Tishla by Purnika Sales</span>
    <span>INDIAN FASHION · SURAT</span>
  </div>
</footer>

<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>