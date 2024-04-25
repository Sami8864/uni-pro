<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'blocked',
        'last_message_at',
        'receiver_muted',
        'sender_muted',
    ];
    protected $casts = [
        'sender_muted' => 'boolean',

        'receiver_muted' => 'boolean'
    ];
    public function getMuteAttribute()
    {
        // Determine if the conversation is muted based on sender_muted or receiver_muted columns
        return $this->sender_muted || $this->receiver_muted;
    }
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function lastest_message()
    {
        return $this->hasMany(Message::class, 'conversation_id')->with('sender')->latest()->first();
    }
    public function lastest_time()
    {
        return $this->hasMany(Message::class, 'conversation_id')->with('sender')->latest()->first();
    }
}
