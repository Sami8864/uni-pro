<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvite extends Model
{
    protected $table = 'user_invitations';
    use HasFactory;
    protected $fillable = ['user_id', 'invitation_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
