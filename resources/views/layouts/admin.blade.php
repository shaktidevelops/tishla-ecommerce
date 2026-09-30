<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#241f1c">
<title>{{ $title ?? 'Tishla Admin' }}</title>
<link rel="icon" type="image/png" href="{{ asset('tishla-favicon.png') }}">
<link rel="stylesheet" href="{{ asset('assets/app.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin.css') }}">
</head>
<body class="admin-body">
<header class="admin-topbar">
  <button class="admin-mobile-toggle" data-admin-menu aria-label="Open admin navigation">☰</button>
  <a class="admin-mobile-brand" href="{{ route('admin.dashboard') }}">
    <img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla"><span>TISHLA ADMIN</span>
  </a>
  <div class="admin-topbar-center"><span>ADMINISTRATION</span><b>{{ now()->format('d M Y') }}</b></div>
  <div class="admin-topbar-right">
    <a href="{{ route('home') }}" target="_blank" rel="noopener">VIEW STORE ↗</a>
    <span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
    <div><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}</small></div>
  </div>
</header>

<div class="admin-shell">
  <aside class="admin-sidebar" data-admin-sidebar>
    <a class="admin-brand" href="{{ route('admin.dashboard') }}">
      <img src="{{ asset('assets/tishla-logo.png') }}" alt="Tishla">
      <div><strong>TISHLA</strong><small>ADMIN</small></div>
    </a>

    <div class="admin-sidebar-caption">OVERVIEW</div>
    <nav>
      <a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><span>01</span>Dashboard</a>
    </nav>

    <div class="admin-sidebar-caption">CATALOG</div>
    <nav>
      <a class="{{ request()->routeIs('admin.products.*')?'active':'' }}" href="{{ route('admin.products.index') }}"><span>02</span>Products</a>
      <a class="{{ request()->routeIs('admin.inventory.*')?'active':'' }}" href="{{ route('admin.inventory.index') }}"><span>03</span>Inventory</a>
      <a class="{{ request()->routeIs('admin.media.*')?'active':'' }}" href="{{ route('admin.media.index') }}"><span>04</span>Media</a>
      <a class="{{ request()->routeIs('admin.masters.*')?'active':'' }}" href="{{ route('admin.masters.index') }}"><span>05</span>Masters</a>
    </nav>

    <div class="admin-sidebar-caption">COMMERCE</div>
    <nav>
      <a class="{{ request()->routeIs('admin.orders.*')?'active':'' }}" href="{{ route('admin.orders.index') }}"><span>06</span>Orders</a>
      <a class="{{ request()->routeIs('admin.customers.*')?'active':'' }}" href="{{ route('admin.customers.index') }}"><span>07</span>Customers</a>
    </nav>

    <div class="admin-sidebar-caption">BRAND</div>
    <nav>
      <a class="{{ request()->routeIs('admin.merchandising.*')?'active':'' }}" href="{{ route('admin.merchandising.index') }}"><span>08</span>Merchandising</a>
    </nav>

    <div class="admin-sidebar-caption">SYSTEM</div>
    <nav>
      <a class="{{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.index') }}"><span>09</span>Settings</a>
    </nav>

    <div class="admin-sidebar-bottom">
      <div class="admin-operator"><span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>Signed in</small></div></div>
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