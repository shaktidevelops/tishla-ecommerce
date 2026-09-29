<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasUuids;
    protected $fillable = ['storage_provider','public_url','alt_text','mime_type','width','height','file_size'];
}
