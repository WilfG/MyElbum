<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = array('contact_id', 'frame_id');

    public function frame(){
        return $this->belongsTo(Frame::class);
    }
    public function contact(){
        return $this->belongsTo(Contact::class);
    }
}
