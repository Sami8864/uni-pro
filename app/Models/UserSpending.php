<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpending extends Model
{
    use HasFactory;

    protected $fillable=['amount','user','spending_type','transaction_type'];
}
