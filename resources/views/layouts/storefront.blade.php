<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Tishla by Purnika Sales — Indian fashion from Surat.' }}">
<link rel="icon" href="{{ asset('favicon.svg') }}">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body>
<header class="site-header">
  <div class="topline">SURAT · INDIA <span>•</span> SHIPPING ACROSS INDIA</div>
  <div class="nav-wrap">
    <a class="brand" href="{{ route('home') }}"><span class="brand-main">TISHLA</span><span class="brand-sub">BY PURNIKA SALES</span></a>
    <button class="mobile-toggle" data-menu-toggle aria-label="Open menu">☰</button>
    <nav class="main-nav" data-nav>
      <a href="{{ route('shop') }}">SHOP</a>
      <a href="{{ route('shop',['department'=>'sarees']) }}">SAREES</a>
      <a href="{{ route('shop',['department'=>'lehengas']) }}">LEHENGAS</a>
      <a href="{{ route('shop',['department'=>'kurtis-sets']) }}">KURTIS & SETS</a>
      <a href="{{ route('page','about') }}">ABOUT</a>
      <a href="{{ route('page','contact') }}">CONTACT</a>
    </nav>
    <div class="nav-actions"><a href="{{ route('shop') }}">⌕</a><a href="{{ route('cart') }}">◌<span class="bag-count">{{ collect(session('cart',[]))->sum('quantity') }}</span></a>@auth<a href="{{ route('admin.dashboard') }}">ADMIN</a>@endauth</div>
  </div>
</header>
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
<main>@yield('content')</main>
<footer class="site-footer">
<div class="footer-grid">
<div><span class="eyebrow">TISHLA</span><h3>Indian craft.<br>Contemporary spirit.</h3><p>Curated fashion from Surat, India.</p></div>
<div><span class="eyebrow">SHOP</span><a href="{{ route('shop') }}">All Products</a><a href="{{ route('shop',['department'=>'sarees']) }}">Sarees</a><a href="{{ route('shop',['department'=>'lehengas']) }}">Lehengas</a><a href="{{ route('cart') }}">Bag</a></div>
<div><span class="eyebrow">HELP</span><a href="{{ route('page','shipping') }}">Shipping</a><a href="{{ route('page','returns') }}">Returns</a><a href="{{ route('page','size-guide') }}">Size Guide</a><a href="{{ route('page','faq') }}">FAQ</a></div>
<div><span class="eyebrow">CONTACT</span><a href="https://wa.me/{{ env('TISHLA_WHATSAPP','919574716712') }}">WhatsApp</a><a href="mailto:{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}">{{ env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com') }}</a><span>Surat, Gujarat</span></div>
</div>
<div class="footer-bottom"><span>© {{ date('Y') }} Tishla by Purnika Sales</span><span>Alive is awesome!</span></div>
</footer>
<script src="{{ asset('assets/app.js') }}"></script>
</body></html>