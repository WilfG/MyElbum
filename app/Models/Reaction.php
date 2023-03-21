<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'user_id', 'contact_id', 'frame_id', 'frame_content_id', 'comment_id', 'content_comment_id'];
}
