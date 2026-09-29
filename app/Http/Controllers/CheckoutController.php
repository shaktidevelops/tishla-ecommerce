<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (! count($cart)) return redirect()->route('shop')->with('error','Your bag is empty.');
        return view('storefront.checkout', compact('cart'));
    }

    public function store(Request $request, OrderService $orders)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120',
            'email'=>'required|email|max:160',
            'phone'=>'required|string|max:30',
            'line1'=>'required|string|max:180',
            'line2'=>'nullable|string|max:180',
            'area'=>'nullable|string|max:120',
            'city'=>'required|string|max:120',
            'state'=>'required|string|max:120',
            'postal_code'=>'required|string|max:20',
            'country'=>'required|string|max:80',
            'payment_method'=>'required|in:cod,online',
        ]);

        $cart = $request->session()->get('cart', []);
        if (! count($cart)) return redirect()->route('shop')->with('error','Your bag is empty.');

        if ($data['payment_method'] === 'online') {
            return back()->withInput()->with('error','Online payment gateway will be enabled after Razorpay configuration.');
        }

        $order = $orders->place(
            ['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']],
            collect($data)->except(['payment_method'])->all(),
            array_values($cart),
            $data['payment_method']
        );

        $request->session()->forget('cart');

        return view('storefront.order-success', compact('order'));
    }
}
