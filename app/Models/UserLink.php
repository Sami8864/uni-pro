<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLink extends Model
{
    use HasFactory;

    protected $fillable=['url', 'user_id','casting_networks','instagram','tiktok'];


    public function userLinks()
    {
        return $this->hasMany(UserLink::class);
    }

}
