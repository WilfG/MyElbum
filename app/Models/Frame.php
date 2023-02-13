<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    use HasFactory;

    protected $fillable = array('frame_title', 'frame_description', 'userID', 'planID', 'status');


    public function plan(){
        return $this->belongsTo(Plan::class);
    }

    public function commments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function frame_contents()
    {
        return $this->hasMany(FrameContent::class);
    }
}
