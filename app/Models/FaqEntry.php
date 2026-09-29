<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class FaqEntry extends Model { use HasUuids; protected $fillable=['category','question','answer_html','sort_order','is_active']; protected $casts=['is_active'=>'boolean']; }
