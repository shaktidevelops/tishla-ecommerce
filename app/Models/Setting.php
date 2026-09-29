<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key','value'];
    protected $casts = ['value'=>'array'];
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
}
