<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasUuids;

    protected $fillable = ['name','email','phone','subject','message','status','source'];
}
