<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameContent extends Model
{
    use HasFactory;

    protected $fillable = array('content_type', 'filepath', 'content_type', 'frame_id');

    public function frame(){
        return $this->belongsTo(Frame::class);
    }

    public function frame_content_comments(){
        return $this->hasMany(FrameContentComment::class);
    }

    public function frame_content_tags(){
        return $this->hasMany(FrameContentTag::class);
    }
}
