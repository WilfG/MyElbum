<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameContentComment extends Model
{
    use HasFactory;

    public function frame_content(){
        return $this->belongsTo(FrameContent::class);
    }
}
