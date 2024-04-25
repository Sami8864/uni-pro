<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEssence extends Model
{
    use HasFactory;
    protected $table='user_essences';
    protected $fillable=['essence_id','user_id'];
}
