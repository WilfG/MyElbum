<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactFriends
 extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'contact_id'];

    public function contact(){
        return $this->belongsTo(Contact::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
