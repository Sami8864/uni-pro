<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'points_required',
        'type',
        'award_image'
    ];
    // public function profileProgress()
    // {
    //     return $this->belongsToMany(ProfileProgress::class, 'user_achievements','profile_progress_id', 'achievement_id');
    // }
    public function profileProgress()
    {
        return $this->belongsToMany(ProfileProgress::class, 'user_achievements', 'profile_progress_id', 'achievement_id')
            ->withPivot('percentage_achieved');
    }
}
