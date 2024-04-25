<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_details';
    use HasFactory;
    protected $fillable = [
        'device_id',
        'name',
        'weight_height',
        'physique',
        'location',
        'essence',
        'type',
        'IMDb',
        'age',
        'ethnicity',
        'gender',
        'height',
        'weight'
    ];

    public function user_type(){
        return $this->hasmany(UserType::class,'user_id');
    }

    public function user_essence(){
        return $this->hasmany(UserEssence::class,'user_id');
    }
    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_feeds');
    }
}
