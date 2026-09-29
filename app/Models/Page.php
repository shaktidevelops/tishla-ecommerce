<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasUuids;
    protected $fillable = ['slug','title','excerpt','body_html','template_key','status','published_at'];
    protected $casts = ['published_at'=>'datetime'];
}
