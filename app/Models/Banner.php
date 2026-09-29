<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model { use HasUuids; protected $fillable=['name','eyebrow','title','description','image_url','mobile_image_url','cta_label','cta_url','sort_order','is_active']; protected $casts=['is_active'=>'boolean']; }
