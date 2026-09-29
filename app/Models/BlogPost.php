<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class BlogPost extends Model { use HasUuids; protected $fillable=['slug','title','excerpt','body_html','cover_image_url','status','published_at']; protected $casts=['published_at'=>'datetime']; }
