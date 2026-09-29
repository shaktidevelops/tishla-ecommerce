<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasUuids;

    protected $fillable = ['name','slug','description','hero_image_url','sort_order','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
