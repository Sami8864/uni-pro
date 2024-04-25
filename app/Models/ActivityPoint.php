<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'upperlimit',
        'lowerlimit',
        'intervalsize',
        'perintervalcontact',
        'perintervalprice'
    ];
    
}
