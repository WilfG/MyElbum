<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameBin extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function frame(){
        return $this->belongsTo(Frame::class);
    }
    public function frame_contents(){
        return $this->belongsTo(FrameContent::class);
    }
}
