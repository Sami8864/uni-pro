<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    use HasFactory;
    protected $table = 'user_achievements';

    protected $fillable=['profile_progress_id ','achievement_id ','percentage_achieved'];
}
