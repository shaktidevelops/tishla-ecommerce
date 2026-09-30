<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.media.index', [
            'media' => MediaAsset::latest()->paginate(40),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:220'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $path = $data['file']->store('', 'public');
        $url = asset('uploads/' . $path);
        $asset = MediaAsset::create([
            'storage_provider' => 'local',
            'public_url' => $url,
            'alt_text' => $data['alt_text'] ?? $data['file']->getClientOriginalName(),
            'mime_type' => $data['file']->getMimeType(),
            'file_size' => $data['file']->getSize(),
        ]);

        if (!empty($data['product_id'])) {
            $product = Product::findOrFail($data['product_id']);
            if (!empty($data['is_primary'])) {
                ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            }
            ProductImage::create([
                'product_id' => $product->id,
                'media_asset_id' => $asset->id,
                'public_url' => $url,
                'alt_text' => $asset->alt_text,
                'sort_order' => (int) ($product->images()->max('sort_order') ?? -1) + 1,
                'is_primary' => !empty($data['is_primary']),
            ]);
        }

        return back()->with('success', 'Media uploaded and catalogue link saved.');
    }

    public function destroy(MediaAsset $media)
    {
        if (str_contains($media->public_url, '/uploads/')) {
            $path = ltrim((string) parse_url($media->public_url, PHP_URL_PATH), '/');
            $relative = preg_replace('#^uploads/#', '', $path);
            if ($relative) Storage::disk('public')->delete($relative);
        }
        ProductImage::where('media_asset_id', $media->id)->delete();
        $media->delete();
        return back()->with('success', 'Media removed.');
    }
}
