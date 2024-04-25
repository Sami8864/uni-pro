<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfileProgress extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    protected $table = 'profile_progress';
    protected $fillable = ['id', 'battery_level', 'device_id', 'account_level', 'types_points', 'invites_points','available_contacts'];
    // In the ProfileProgress model
    // public function achievements()
    // {
    //     return $this->belongsToMany(Achievement::class, 'user_azchievements', 'profile_progress_id', 'achievement_id');
    // }
    public function achievementsWithPercentage()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements', 'profile_progress_id', 'achievement_id')
            ->withPivot('percentage_achieved');
    }
    public function user_details_No()
    {
        return $this->hasOne(UserDetail::class, 'device_id');
    }
    public function user_details()
    {
        $device = $this->hasMany(UserDetail::class, 'device_id');
        return $device->with('user_type')->with('user_essence');
    }
    public function user_headshots()
    {
        return $this->hasMany(Headshots::class, 'device_id');
    }

    public function user_attributes()
    {
        $headshots=$this->hasMany(Headshots::class, 'device_id');
        return $headshots->with('headshots');
    }
    public function user()
    {
        return $this->hasOne(User::class);
    }
    protected $appends = ['referral_link'];
    public function getReferralLinkAttribute()
    {
        return $this->referral_link = route('invite',  $this->device_id);
    }
    public function getdata()
    {
        $data['user_details'] = $this->user_details ? $this->user_details : new UserDetail();
        $data['user_attributes'] = $this->user_attributes;
       return $data;
    }
}
