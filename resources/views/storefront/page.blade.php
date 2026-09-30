@extends('layouts.storefront')
@section('content')
<section class="inner-hero compact-hero"><div><span class="eyebrow">TISHLA</span><h1>{{ $page->title }}</h1><p>{{ $page->excerpt }}</p></div><div class="inner-hero-mark">T</div></section>
<section class="content-shell"><div class="content-card">{!! $page->body_html ?: '<p>Content is being prepared in the Tishla Admin.</p>' !!}</div></section>
@endsection
