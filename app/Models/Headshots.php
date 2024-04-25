<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Headshots extends Model
{
    use HasFactory;
    protected $fillable=['device_id','url','type_id'];

   public function headshots(){
    return $this->hasMany(UserAttribute::class,'headshot');
   }
   
  
}
