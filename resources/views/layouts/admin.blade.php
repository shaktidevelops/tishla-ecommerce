<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title ?? 'Tishla Control Room' }}</title><link rel="icon" href="{{ asset('favicon.svg') }}"><link rel="stylesheet" href="{{ asset('assets/app.css') }}"></head>
<body class="admin-body">
<header class="admin-mobile-head"><a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-main">TISHLA</span><span class="brand-sub">CONTROL ROOM</span></a><button data-admin-menu aria-label="Open admin menu">☰</button></header>
<div class="admin-shell">
<aside class="admin-sidebar" data-admin-sidebar>
<a class="admin-brand" href="{{ route('admin.dashboard') }}"><strong>TISHLA</strong><small>CONTROL ROOM</small></a>
<div class="admin-user"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role }}</span></div>
<nav>
<a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
<a class="{{ request()->routeIs('admin.products.*')?'active':'' }}" href="{{ route('admin.products.index') }}">Catalogue</a>
<a class="{{ request()->routeIs('admin.orders.*')?'active':'' }}" href="{{ route('admin.orders.index') }}">Orders</a>
<a class="{{ request()->routeIs('admin.customers.*')?'active':'' }}" href="{{ route('admin.customers.index') }}">Customers</a>
<a class="{{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.index') }}">Settings</a>
</nav>
<div class="admin-bottom"><a href="{{ route('home') }}">View Storefront</a><form method="post" action="{{ route('admin.logout') }}">@csrf<button>Sign out</button></form></div>
</aside>
<section class="admin-main">
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
@yield('content')
</section></div>
<script src="{{ asset('assets/app.js') }}"></script>
</body></html>