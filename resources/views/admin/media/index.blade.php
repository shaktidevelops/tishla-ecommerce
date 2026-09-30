@extends('layouts.admin')
@section('content')
<div class="admin-page-head"><div><span class="eyebrow">MEDIA STUDIO</span><h1>Brand & product imagery</h1><p>Upload, organise and attach premium imagery directly to catalogue products.</p></div></div>
<section class="admin-card media-upload-card"><form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="upload-grid">@csrf
<label class="field media-file"><span>Image asset</span><input type="file" name="file" accept="image/*" required></label>
<label class="field"><span>Attach to product</span><select name="product_id"><option value="">Brand / unassigned media</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} · {{ $product->sku }}</option>@endforeach</select></label>
<label class="field"><span>Alt text</span><input name="alt_text" placeholder="Describe the image for accessibility and SEO"></label>
<label class="media-primary"><input type="checkbox" name="is_primary" value="1"> Make this the product primary image</label>
<button class="btn btn-dark">UPLOAD & ATTACH ↗</button></form></section>
<section class="media-grid">@forelse($media as $item)<article class="media-card premium-media"><img loading="lazy" src="{{ $item->public_url }}" alt="{{ $item->alt_text }}"><div class="media-meta"><div><strong>{{ $item->alt_text }}</strong><small>{{ strtoupper($item->mime_type ?? 'IMAGE') }} · {{ $item->file_size ? number_format($item->file_size/1024,0).' KB' : '—' }}</small></div><form method="post" action="{{ route('admin.media.destroy',$item) }}">@csrf @method('DELETE')<button class="text-button">Remove</button></form></div></article>@empty<div class="empty">No media uploaded yet. Your visual library starts here.</div>@endforelse</section>
@endsection
