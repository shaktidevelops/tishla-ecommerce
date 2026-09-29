<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUuids;
    protected $fillable = ['order_id','product_id','variant_id','sku','product_name','variant_name','quantity','unit_price','discount_total','taxable_total','tax_rate','tax_total','line_total','image_url'];
    protected $casts = ['unit_price'=>'decimal:2','discount_total'=>'decimal:2','taxable_total'=>'decimal:2','tax_rate'=>'decimal:2','tax_total'=>'decimal:2','line_total'=>'decimal:2'];
}
