<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasUuids;

    protected $fillable = ['customer_id','label','name','line1','line2','area','city','state','postal_code','country','phone','is_default'];
    protected $casts = ['is_default'=>'boolean'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
