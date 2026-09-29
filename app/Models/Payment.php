<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuids;
    protected $fillable = ['order_id','provider','provider_payment_id','provider_order_id','amount','currency','status','method','payload','paid_at'];
    protected $casts = ['payload'=>'array','amount'=>'decimal:2','paid_at'=>'datetime'];
}
