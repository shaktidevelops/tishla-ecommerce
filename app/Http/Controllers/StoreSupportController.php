<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Order;
use Illuminate\Http\Request;

class StoreSupportController extends Controller
{
    public function tracking(Request $request)
    {
        $order = null;
        if ($request->filled('order_number') && ($request->filled('email') || $request->filled('phone'))) {
            $query = Order::with(['items','shipments'])->where('order_number', trim($request->string('order_number')));
            if ($request->filled('email')) {
                $query->whereRaw('LOWER(customer_email) = ?', [strtolower(trim($request->string('email')))]);
            } else {
                $query->where('customer_phone', trim($request->string('phone')));
            }
            $order = $query->first();
        }

        return view('storefront.track-order', compact('order'));
    }

    public function contact()
    {
        return view('storefront.contact');
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:140',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        Enquiry::create($data + ['status' => 'new', 'source' => 'website']);

        return back()->with('success', 'Thank you. Your message is with the Tishla team. We will get back to you shortly.');
    }
}
