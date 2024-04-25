<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InitialSurvey extends Model
{
    use HasFactory;
    protected $fillable=['option', 'answer','image','points'];
    use HasFactory;
}
