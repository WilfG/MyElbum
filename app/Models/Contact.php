<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function comments(){
        return $this->hasMany(Comment::class);
    }
    public function tags(){
        return $this->hasMany(Tag::class);
    }
}
