<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = array('comment_description', 'frameID', 'contactID');

    public function frame(){
        return $this->belongsTo(Frame::class);
    }
}
