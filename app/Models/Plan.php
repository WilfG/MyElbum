<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = array('plan_title', 'duration_time','plan_type', 'storage_capacity', 'price');

    public function frame(){
        return $this->hasOne(Frame::class);
    }
    
    public function souscriptions(){
        return $this->hasMany(Souscription::class);
    }
}
