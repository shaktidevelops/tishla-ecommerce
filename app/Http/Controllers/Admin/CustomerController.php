<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers=Customer::withCount('orders')
            ->when($request->filled('q'),function($q)use($request){
                $term='%'.$request->string('q')->toString().'%';
                $q->where(fn($s)=>$s->where('first_name','like',$term)->orWhere('last_name','like',$term)->orWhere('email','like',$term)->orWhere('phone','like',$term));
            })->latest()->paginate(30)->withQueryString();
        return view('admin.customers.index',compact('customers'));
    }
}
