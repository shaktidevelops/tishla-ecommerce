<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $fillable = ['order_number','customer_id','status','currency','subtotal','discount_total','taxable_total','tax_total','shipping_total','grand_total','customer_name','customer_email','customer_phone','billing_address','shipping_address','notes','source','payment_method','external_payment_reference','external_checkout_reference','placed_at','confirmed_at','cancelled_at','delivered_at'];
    protected $casts = ['billing_address'=>'array','shipping_address'=>'array','subtotal'=>'decimal:2','discount_total'=>'decimal:2','taxable_total'=>'decimal:2','tax_total'=>'decimal:2','shipping_total'=>'decimal:2','grand_total'=>'decimal:2','placed_at'=>'datetime','confirmed_at'=>'datetime','cancelled_at'=>'datetime','delivered_at'=>'datetime'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function shipments(): HasMany { return $this->hasMany(Shipment::class); }
}
