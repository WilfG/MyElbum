<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'id',
    //     'notification_sounds',
    //     'notification_vibrate',
    //     'notification_contentComments_reactions',
    //     'notification_add_content_to_profile_album',
    //     'notification_new_tag_in_content',
    //     'notification_content_deleted',
    // ];

    protected $guarded = [];


    public function user(){
        return $this->belongsTo(User::class);
    }
}
