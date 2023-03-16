<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CommentFile;
use App\Models\User;
use App\Http\Requests\CommentRequest;
use Gregwar\Captcha\CaptchaBuilder;
use App\Events\NewRecordCreated;
use Illuminate\Support\Facades\Cache;

class CommentsController extends Controller
{

    public function getComments(Request $request)
    {

        $sort_by = $request->input('sort_by', 'comments.created_at');
        $order_by = $request->input('order_by', 'desc');

        $sort_by = $this->sortComments($sort_by);

        $comments = [];

        $totalComments = Comment::where('comment_reply_id', null)->count();
        $totalPages = ceil($totalComments / 25);

        $primeComments = Comment::select('comments.id', 'users.name', 'users.email', 'comments.text', 'comments.created_at')
            ->where('comment_reply_id', null)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->orderBy($sort_by, $order_by)
            ->paginate(25);

        $commentsAmount = Comment::where('comment_reply_id', null)->get();

        foreach ($primeComments as $primeComment) {
            $comment = [
                'id' => $primeComment->id,
                'name' => $primeComment->name,
                'email' => $primeComment->email,
                'text' => $primeComment->text,
                'created_at' => $primeComment->created_at->format('Y-m-d \i\n H:i'),
                'replies' => [],
                'files' => $primeComment->files,
            ];

            $this->getReplyComments($primeComment->id, $comment);

            $comments[] = $comment;
        }

        $data = [
            'comments' => $comments,
            'totalPages' => $totalPages,
            'commentsAmount' => $commentsAmount,
        ];

        return response()->json($data);
    }


    private function getReplyComments($commentId, &$comment)
    {
        $replyComments = Comment::select('comments.id', 'users.name', 'users.email', 'comments.text', 'comments.created_at')
            ->where('comment_reply_id', $commentId)
            ->orderBy('comments.created_at', 'asc')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->get();

        foreach ($replyComments as $replyComment) {
            $reply = [
                'id' => $replyComment->id,
                'name' => $replyComment->name,
                'email' => $replyComment->email,
                'text' => $replyComment->text,
                'created_at' => $replyComment->created_at->format('Y-m-d \i\n H:i'),
                'replies' => [],
                'files' => $replyComment->files,
            ];

            $this->getReplyComments($replyComment->id, $reply);

            $comment['replies'][] = $reply;
        }
    }

    public function storeComment(CommentRequest $request)
    {

        $user = User::firstOrCreate([
            'name' => $request->username,
            'homepage' => $request->homepage,
            'email' => $request->email,
        ]);

        $comment = Comment::create([
            'text' => $request->comment,
            'user_id' => $user->id,
            'comment_reply_id' => $request->replyId,
        ]);

        return true;
    }

    private function sortComments($sort_by)
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
