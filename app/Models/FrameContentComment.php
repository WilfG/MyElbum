<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameContentComment extends Model
{
    use HasFactory;

    protected $fillable = array('content_comment', 'frame_id', 'contact_id');


    public function frame_content(){
        return $this->belongsTo(FrameContent::class);
    }

    public function contact(){
        return $this->belongsTo(Contact::class);
    }
}
