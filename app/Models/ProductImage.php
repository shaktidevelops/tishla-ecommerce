<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasUuids;

    protected $fillable = ['product_id','media_asset_id','public_url','alt_text','sort_order','is_primary'];
    protected $casts = ['is_primary'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
