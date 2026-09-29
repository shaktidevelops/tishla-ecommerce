<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasUuids;

    protected $fillable = ['product_id','sku','name','size_name','color_name','color_hex','price','compare_at_price','is_active'];
    protected $casts = ['price'=>'decimal:2','compare_at_price'=>'decimal:2','is_active'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
