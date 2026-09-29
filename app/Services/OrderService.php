<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function place(array $customerData, array $shipping, array $cart, string $paymentMethod = 'cod'): Order
    {
        return DB::transaction(function () use ($customerData,$shipping,$cart,$paymentMethod) {
            $customer = Customer::updateOrCreate(
                ['email'=>$customerData['email']],
                ['phone'=>$customerData['phone'],'first_name'=>$customerData['name'],'customer_type'=>'retail','status'=>'active']
            );

            $subtotal = collect($cart)->sum(fn($row) => $row['price'] * $row['quantity']);
            $gstRate = (float)env('TISHLA_GST_RATE',5);
            $tax = round($subtotal * $gstRate / 100, 2);
            $grand = $subtotal + $tax;

            $order = Order::create([
                'order_number'=>'TIS-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'customer_id'=>$customer->id,
                'status'=>'pending_payment',
                'currency'=>env('TISHLA_CURRENCY','INR'),
                'subtotal'=>$subtotal,
                'taxable_total'=>$subtotal,
                'tax_total'=>$tax,
                'shipping_total'=>0,
                'grand_total'=>$grand,
                'customer_name'=>$customerData['name'],
                'customer_email'=>$customerData['email'],
                'customer_phone'=>$customerData['phone'],
                'shipping_address'=>$shipping,
                'source'=>'web',
                'payment_method'=>$paymentMethod,
                'placed_at'=>now(),
            ]);

            foreach ($cart as $row) {
                $lineBase = $row['price'] * $row['quantity'];
                $lineTax = round($lineBase * $gstRate / 100,2);
                $order->items()->create([
                    'product_id'=>$row['product_id'],
                    'variant_id'=>$row['variant_id'],
                    'sku'=>$row['sku'],
                    'product_name'=>$row['name'],
                    'variant_name'=>$row['variant_name'],
                    'quantity'=>$row['quantity'],
                    'unit_price'=>$row['price'],
                    'taxable_total'=>$lineBase,
                    'tax_rate'=>$gstRate,
                    'tax_total'=>$lineTax,
                    'line_total'=>$lineBase+$lineTax,
                    'image_url'=>$row['image_url'],
                ]);
            }

            $order->payments()->create([
                'provider'=>$paymentMethod === 'cod' ? 'cod' : 'razorpay',
                'amount'=>$grand,
                'currency'=>env('TISHLA_CURRENCY','INR'),
                'status'=>'pending',
                'method'=>$paymentMethod,
            ]);

            if ($paymentMethod === 'cod') {
                $order->update(['status'=>'confirmed','confirmed_at'=>now()]);
            }

            return $order;
        });
    }
}
