<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = array('contactID', 'frameID');

    public function frame(){
        return $this->belongsTo(Frame::class);
    }
}
