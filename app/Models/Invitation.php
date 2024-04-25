<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;
    protected $fillable = ['inviter_id', 'token', 'recipient'];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function userInvites()
    {
        return $this->hasMany(UserInvite::class);
    }
}
