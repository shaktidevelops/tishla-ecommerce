<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasUuids;
    protected $fillable = ['order_id','provider','tracking_number','label_url','status','shipping_method','shipping_cost','payload','shipped_at','delivered_at'];
    protected $casts = ['payload'=>'array','shipping_cost'=>'decimal:2','shipped_at'=>'datetime','delivered_at'=>'datetime'];
}
