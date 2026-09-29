<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasUuids;

    protected $fillable = ['name','slug','description','hero_image_url','banner_image_url','collection_type','sort_order','is_featured','is_active','starts_at','ends_at'];
    protected $casts = ['is_featured'=>'boolean','is_active'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime'];
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class,'collection_products')->withPivot('sort_order'); }
}
