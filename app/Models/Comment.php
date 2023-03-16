<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CommentFile;


class Comment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'text',
        'homepage',
        'user_id',
        'comment_reply_id'
    ];

    public function files()
    {
        return $this->hasMany(CommentFile::class);
    }
}
