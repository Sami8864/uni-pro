<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkType extends Model
{
    use HasFactory;

    protected $fillable=['link_type'];

    public function linkType()
    {
        return $this->belongsTo(LinkType::class);
    }
}
