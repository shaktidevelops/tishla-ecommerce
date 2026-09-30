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
        $now = now();
        $validStatuses = ['pending_payment','paid','confirmed','processing','packed','shipped','delivered','returned','refunded'];
        $revenueFilter = fn($q) => $q->whereIn('status', $validStatuses);

        $monthlyRevenue = [];
        $monthlyOrders = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i)->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $monthlyRevenue[] = (float) Order::whereBetween('created_at', [$month, $end])->whereIn('status', $validStatuses)->sum('grand_total');
            $monthlyOrders[] = Order::whereBetween('created_at', [$month, $end])->count();
        }

        $statusCounts = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->orderByDesc('total')->get()
            ->mapWithKeys(fn($row) => [$row->status => (int) $row->total])->all();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $validStatuses)
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as units'), DB::raw('SUM(order_items.line_total) as sales'))
            ->groupBy('order_items.product_name')->orderByDesc('units')->limit(5)->get();

        $todayRevenue = (float) Order::whereDate('created_at', $now->toDateString())->whereIn('status', $validStatuses)->sum('grand_total');
        $orders30 = Order::where('created_at','>=',$now->copy()->subDays(30))->count();
        $revenue30 = (float) Order::where('created_at','>=',$now->copy()->subDays(30))->whereIn('status',$validStatuses)->sum('grand_total');
        $averageOrder = $orders30 > 0 ? $revenue30 / $orders30 : 0;

        return view('admin.dashboard', [
            'activeProducts'=>Product::where('status','active')->count(),
            'customers'=>Customer::where('status','active')->count(),
            'orders30'=>$orders30,
            'revenue30'=>$revenue30,
            'todayRevenue'=>$todayRevenue,
            'averageOrder'=>$averageOrder,
            'pendingOrders'=>Order::whereIn('status',['pending_payment','paid','confirmed','processing','packed'])->count(),
            'lowStock'=>DB::table('inventory_stock')->whereRaw('(on_hand - reserved) <= reorder_level')->count(),
            'recentOrders'=>Order::latest()->take(8)->get(),
            'monthlyRevenue'=>$monthlyRevenue,
            'monthlyOrders'=>$monthlyOrders,
            'monthLabels'=>collect(range(11,0))->map(fn($i)=>$now->copy()->subMonths($i)->format('M'))->all(),
            'statusCounts'=>$statusCounts,
            'topProducts'=>$topProducts,
        ]);
    }
}
