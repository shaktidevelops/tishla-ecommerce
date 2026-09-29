<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'activeProducts'=>Product::where('status','active')->count(),
            'customers'=>Customer::where('status','active')->count(),
            'orders30'=>Order::where('created_at','>=',now()->subDays(30))->count(),
            'revenue30'=>Order::where('created_at','>=',now()->subDays(30))->whereNotIn('status',['cancelled','payment_failed'])->sum('grand_total'),
            'pendingOrders'=>Order::whereIn('status',['pending_payment','confirmed','processing','packed'])->count(),
            'lowStock'=>DB::table('inventory_stock')->whereRaw('(on_hand - reserved) <= reorder_level')->count(),
            'recentOrders'=>Order::latest()->take(8)->get(),
        ]);
    }
}
