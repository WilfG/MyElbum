<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_verification extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(){
        $this->belongsTo(User::class);
    }
}
