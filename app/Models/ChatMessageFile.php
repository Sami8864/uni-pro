<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessageFile extends Model
{
    use HasFactory;
    protected $fillable=['chat_message_id' ,'file_path' ,  ];
    public function chatMessage(){
        return $this->belongsTo(Message::class);
    }
}
