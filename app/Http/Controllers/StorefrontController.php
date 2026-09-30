<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Department;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        $recentIds = $request->session()->get('recently_viewed', []);

        return view('storefront.home', [
            'featured' => Product::with(['department','images'])->where('status','active')->where('featured',true)->latest()->take(8)->get(),
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
            'recentlyViewed' => Product::with(['department','images'])->where('status','active')->whereIn('id',$recentIds)->get()
                ->sortBy(fn ($product) => array_search((string)$product->id, $recentIds, true) ?? 999)->values(),
        ]);
    }

    public function shop(Request $request)
    {
        $sort = $request->string('sort','newest')->toString();
        $query = Product::with(['department','images','variants'])->where('status','active')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($sub) => $sub->where('name','like',$term)->orWhere('sku','like',$term)->orWhere('fabric','like',$term)->orWhere('product_type','like',$term));
            })
            ->when($request->filled('department'), fn ($query) => $query->whereHas('department', fn ($dep) => $dep->where('slug',$request->string('department')->toString())))
            ->when($request->filled('min_price'), fn ($query) => $query->where('base_price','>=',(float)$request->input('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('base_price','<=',(float)$request->input('max_price')));

        if ($request->filled('collection')) $query->whereHas('collections', fn ($collection) => $collection->where('slug',$request->string('collection')->toString()));

        match ($sort) {
            'price_asc' => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $products = $query->paginate(24)->withQueryString();

        return view('storefront.shop', [
            'products' => $products,
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
            'collections' => Collection::where('is_active',true)->orderBy('sort_order')->orderBy('name')->get(),
            'wishlist' => collect($request->session()->get('wishlist', []))->map(fn ($id) => (string)$id)->values(),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'department' => $request->string('department')->toString(),
                'collection' => $request->string('collection')->toString(),
                'sort' => $sort,
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
            ],
        ]);
    }

    public function product(Request $request, string $slug)
    {
        $product = Product::with(['department','variants','images','collections'])->where('slug',$slug)->where('status','active')->firstOrFail();

        $recent = collect($request->session()->get('recently_viewed', []))->prepend((string)$product->id)->unique()->take(6)->values()->all();
        $request->session()->put('recently_viewed',$recent);

        $recentProducts = Product::with(['department','images'])->where('status','active')->whereIn('id',array_slice($recent,1))
            ->get()->sortBy(fn ($item) => array_search((string)$item->id,$recent,true) ?? 999)->values();

        return view('storefront.product', [
            'product'=>$product,
            'recentProducts'=>$recentProducts,
            'commerceDefaults'=>Setting::where('key','commerce_defaults')->value('value') ?? [],
            'wishlist'=>collect($request->session()->get('wishlist', []))->contains((string)$product->id),
        ]);
    }

    public function wishlist(Request $request)
    {
        $ids = collect($request->session()->get('wishlist', []))->map(fn ($id) => (string)$id)->unique()->values();
        $products = Product::with(['department','images'])->where('status','active')->whereIn('id',$ids)->get()
            ->sortBy(fn ($item) => $ids->search((string)$item->id))->values();

        return view('storefront.wishlist', compact('products'));
    }

    public function toggleWishlist(Request $request, Product $product)
    {
        abort_unless($product->status === 'active', 404);
        $ids = collect($request->session()->get('wishlist', []))->map(fn ($id) => (string)$id)->values();
        $id = (string)$product->id;
        $saved = $ids->contains($id);
        $ids = $saved ? $ids->reject(fn ($item) => $item === $id)->values() : $ids->prepend($id)->unique()->values()->take(50);
        $request->session()->put('wishlist',$ids->all());

        return back()->with('success', $saved ? 'Removed from wishlist.' : 'Saved to wishlist.');
    }

    public function page(string $slug)
    {
        $page = Page::where('slug',$slug)->where('status','published')->firstOrFail();
        return view('storefront.page', compact('page'));
    }
}
