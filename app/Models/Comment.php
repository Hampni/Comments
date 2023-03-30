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
        return $this->hasMany(CommentFile::class, 'comment_id');
    }

    public static function getComments($params, $primeCommentId = null)
    {
        $order_by = isset($params['order_by']) ? $params['order_by'] : 'asc';

        $sort_by = isset($params['sort_by']) ? self::sortComments($params['sort_by']) : 'comments.created_at';

        $query = self::select('comments.id', 'users.name', 'users.email', 'comments.text', 'comments.created_at')
            ->with('files')
            ->where('comment_reply_id', $primeCommentId === null ? null : $primeCommentId)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->orderBy($sort_by, $order_by)
            ->paginate($params['paginate']);

        return $query;
    }

    private static function sortComments($sort_by)
    {
        switch ($sort_by) {
            case 'name':
                $sort_by = 'users.name';
                break;
            case 'created_at':
                $sort_by = 'comments.created_at';
                break;
            case 'email':
                $sort_by = 'users.email';
                break;
        }
        return $sort_by;
    }
}
