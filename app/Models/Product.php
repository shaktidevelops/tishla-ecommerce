<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'sku','slug','name','short_description','description','department_id','fabric','product_type','shoot_type',
        'base_price','gst_rate','status','featured','min_order_qty','product_badge','care_instructions','shipping_notes','fit_notes'
    ];
    protected $casts = ['base_price'=>'decimal:2','gst_rate'=>'decimal:2','featured'=>'boolean'];
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
    public function images(): HasMany { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function collections(): BelongsToMany { return $this->belongsToMany(Collection::class,'collection_products')->withPivot('sort_order'); }
}
