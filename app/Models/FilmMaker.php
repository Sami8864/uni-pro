<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FilmMaker extends Model
{
    use HasFactory;
    protected $fillable = ['user_id' , 'profile_image', 'compnay_name','full_name', 'bio', 'imdb_link', 'actoraccess_link', 'casting_link','union_id','cover_image' ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getData()
    {
        $data['id'] = $this->id;      
        $data['user_id'] = $this->user_id;
        $data['cover_image'] = $this->cover_image;
        $data['profile_image'] = $this->profile_image;
        $data['compnay_name'] = $this->compnay_name;
        $data['full_name'] = $this->full_name;
        $data['bio'] = $this->bio;
        $data['imdb_link'] = $this->imdb_link;
        $data['actoraccess_link'] = $this->actoraccess_link;
        $data['casting_link'] = $this->casting_link;
        $data['union_id'] = $this->union_id;
        return $data;
    }
}
