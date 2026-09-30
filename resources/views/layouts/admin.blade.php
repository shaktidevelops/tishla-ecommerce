<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#5a0920">
<title>{{ $title ?? 'Tishla Control Room' }}</title>
<link rel="icon" type="image/png" href="{{ asset('tishla-favicon.png') }}">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}"><link rel="stylesheet" href="{{ asset('assets/admin.css') }}">
</head>
<body class="admin-body">
<header class="admin-topbar">
  <div class="admin-mobile-brand"><img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla"><span>CONTROL ROOM</span></div>
  <button class="admin-mobile-toggle" data-admin-menu aria-label="Open Control Room menu">☰</button>
  <div class="admin-topbar-center"><span>COMMERCE OPERATIONS</span><strong>{{ now()->format('D, d M Y') }}</strong></div>
  <div class="admin-topbar-right"><a href="{{ route('home') }}" target="_blank" rel="noopener">STORE ↗</a><span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->role }}</small></div></div>
</header>

<div class="admin-shell">
  <aside class="admin-sidebar" data-admin-sidebar>
    <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla"><div><strong>TISHLA</strong><small>CONTROL ROOM</small></div></a>
    <div class="admin-sidebar-caption">OPERATIONS</div>
    <nav>
      <a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><span>⌂</span>Command Center</a>
      <a class="{{ request()->routeIs('admin.products.*')?'active':'' }}" href="{{ route('admin.products.index') }}"><span>◈</span>Catalogue</a>
      <a class="{{ request()->routeIs('admin.inventory.*')?'active':'' }}" href="{{ route('admin.inventory.index') }}"><span>▦</span>Inventory</a>
      <a class="{{ request()->routeIs('admin.orders.*')?'active':'' }}" href="{{ route('admin.orders.index') }}"><span>↗</span>Orders</a>
      <a class="{{ request()->routeIs('admin.customers.*')?'active':'' }}" href="{{ route('admin.customers.index') }}"><span>♙</span>Customers</a>
    </nav>
    <div class="admin-sidebar-caption">BRAND & CONTENT</div>
    <nav>
      <a class="{{ request()->routeIs('admin.merchandising.*')?'active':'' }}" href="{{ route('admin.merchandising.index') }}"><span>✦</span>Merchandising</a>
      <a class="{{ request()->routeIs('admin.media.*')?'active':'' }}" href="{{ route('admin.media.index') }}"><span>▧</span>Media Studio</a>
    </nav>
    <div class="admin-sidebar-caption">DATA & SYSTEM</div>
    <nav>
      <a class="{{ request()->routeIs('admin.masters.*')?'active':'' }}" href="{{ route('admin.masters.index') }}"><span>≡</span>Catalogue Masters</a>
      <a class="{{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.index') }}"><span>⚙</span>Settings</a>
    </nav>
    <div class="admin-sidebar-bottom">
      <div class="admin-operator"><span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div><b>{{ auth()->user()->name }}</b><small>Signed in</small></div></div>
      <form method="post" action="{{ route('admin.logout') }}">@csrf<button>Sign out</button></form>
    </div>
  </aside>

  <main class="admin-main">
    @if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="flash error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @yield('content')
  </main>
</div>
<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>