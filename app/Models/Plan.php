<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = array('plan_title', 'duration_time', 'storage_capacity', 'userID');

    public function frames(){
        return $this->hasMany(Frame::class);
    }
}
