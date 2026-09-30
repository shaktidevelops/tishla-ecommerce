<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#6e102b"><title>{{ $title ?? 'Tishla Control Room' }}</title><link rel="icon" type="image/png" href="{{ asset('tishla-favicon.png') }}"><link rel="stylesheet" href="{{ asset('assets/app.css') }}"><link rel="stylesheet" href="{{ asset('assets/admin.css') }}"></head>
<body class="admin-body">
<header class="admin-mobile-head"><a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-main">TISHLA</span><span class="brand-sub">CONTROL ROOM</span></a><button data-admin-menu aria-label="Open admin menu">☰</button></header>
<div class="admin-shell">
<aside class="admin-sidebar" data-admin-sidebar>
<a class="admin-brand" href="{{ route('admin.dashboard') }}"><strong>TISHLA</strong><small>CONTROL ROOM</small></a>
<div class="admin-user"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role }}</span></div>
<nav>
<a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
<span class="admin-nav-label">COMMERCE</span>
<a class="{{ request()->routeIs('admin.products.*')?'active':'' }}" href="{{ route('admin.products.index') }}">Catalogue</a>
<a class="{{ request()->routeIs('admin.orders.*')?'active':'' }}" href="{{ route('admin.orders.index') }}">Orders</a>
<a class="{{ request()->routeIs('admin.customers.*')?'active':'' }}" href="{{ route('admin.customers.index') }}">Customers</a>
<span class="admin-nav-label admin-nav-group">BRAND STUDIO</span>
<a class="{{ request()->routeIs('admin.merchandising.*')?'active':'' }}" href="{{ route('admin.merchandising.index') }}">Merchandising</a>
<a class="{{ request()->routeIs('admin.media.*')?'active':'' }}" href="{{ route('admin.media.index') }}">Media Studio</a>
<span class="admin-nav-label admin-nav-group">SYSTEM</span>
<a class="{{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.index') }}">Settings</a>
</nav>
<div class="admin-bottom"><a href="{{ route('home') }}" target="_blank" rel="noopener">View Storefront ↗</a><form method="post" action="{{ route('admin.logout') }}">@csrf<button>Sign out</button></form></div>
</aside>
<section class="admin-main">
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
@yield('content')
</section></div>
<script src="{{ asset('assets/app.js') }}"></script>
</body></html>