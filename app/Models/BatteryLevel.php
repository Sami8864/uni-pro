<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatteryLevel extends Model
{
    use HasFactory;
    protected $fillable=['level'];
}
