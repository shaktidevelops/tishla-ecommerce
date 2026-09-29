<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        return view('storefront.home', [
            'featured' => Product::with(['department','images'])
                ->where('status','active')->where('featured',true)->latest()->take(8)->get(),
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function shop(Request $request)
    {
        $products = Product::with(['department','images'])
            ->where('status','active')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($sub) => $sub->where('name','like',$term)->orWhere('sku','like',$term)->orWhere('fabric','like',$term));
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                $query->whereHas('department', fn ($dep) => $dep->where('slug',$request->string('department')->toString()));
            })
            ->latest()->paginate(24)->withQueryString();

        return view('storefront.shop', [
            'products' => $products,
            'departments' => Department::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }

    public function product(string $slug)
    {
        $product = Product::with(['department','variants','images','collections'])
            ->where('slug',$slug)->where('status','active')->firstOrFail();

        return view('storefront.product', compact('product'));
    }

    public function page(string $slug)
    {
        $page = Page::where('slug',$slug)->where('status','published')->firstOrFail();
        return view('storefront.page', compact('page'));
    }
}
