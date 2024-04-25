<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use Notifiable;
    use HasFactory;
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
    ];
    protected $casts = [
        'data' => 'json',
    ];
}
