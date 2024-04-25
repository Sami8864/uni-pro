<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Essence extends Model
{
    use HasFactory;
protected $table='essences';
    protected $fillable=['name'];
}
