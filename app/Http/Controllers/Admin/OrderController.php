<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const NEXT=[
        'pending_payment'=>['paid','payment_failed','cancelled'],
        'paid'=>['confirmed','cancelled','refunded'],
        'confirmed'=>['processing','cancelled'],
        'processing'=>['packed','cancelled'],
        'packed'=>['shipped'],
        'shipped'=>['delivered','returned'],
        'delivered'=>['returned'],
        'payment_failed'=>['pending_payment','cancelled'],
        'cancelled'=>['refunded'],
        'returned'=>['refunded'],
        'refunded'=>[],
    ];

    public function index(Request $request)
    {
        $orders=Order::query()
            ->when($request->filled('q'),function($q)use($request){
                $term='%'.$request->string('q')->toString().'%';
                $q->where(fn($s)=>$s->where('order_number','like',$term)->orWhere('customer_name','like',$term)->orWhere('customer_phone','like',$term)->orWhere('customer_email','like',$term));
            })
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')->toString()))
            ->latest()->paginate(30)->withQueryString();
        return view('admin.orders.index',compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items','payments','shipments']);
        return view('admin.orders.show',['order'=>$order,'nextStatuses'=>self::NEXT[$order->status]??[]]);
    }

    public function status(Request $request, Order $order)
    {
        $data=$request->validate(['status'=>'required|string','note'=>'nullable|string|max:1000']);
        abort_unless(in_array($data['status'],self::NEXT[$order->status]??[],true),422);
        DB::transaction(function()use($data,$order){
            $old=$order->status;
            $order->update([
                'status'=>$data['status'],
                'confirmed_at'=>$data['status']==='confirmed'?($order->confirmed_at?:now()):$order->confirmed_at,
                'cancelled_at'=>$data['status']==='cancelled'?now():$order->cancelled_at,
                'delivered_at'=>$data['status']==='delivered'?now():$order->delivered_at,
            ]);
            DB::table('order_status_history')->insert([
                'order_id'=>$order->id,'from_status'=>$old,'to_status'=>$data['status'],
                'note'=>$data['note']??null,'changed_by'=>auth()->id(),'created_at'=>now(),'updated_at'=>now(),
            ]);
        });
        return back()->with('success','Order status updated.');
    }
}
