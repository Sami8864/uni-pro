<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFlag extends Model
{
    use HasFactory;
    protected $fillable=['flag_id','user_id','headshot_id'];

}
