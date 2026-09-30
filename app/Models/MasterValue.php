<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MasterValue extends Model
{
    use HasUuids;

    protected $fillable = ['master_type','name','slug','description','sort_order','is_active'];

    protected $casts = ['is_active' => 'boolean'];
}