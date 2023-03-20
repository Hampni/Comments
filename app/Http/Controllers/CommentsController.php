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

    public function getCapthca()
    {
        $builder = new CaptchaBuilder;
        $builder->build();

        $captchaText = $builder->getPhrase();

        return response($builder->output())
            ->header('Content-Type', 'image/png')
            ->header('X-Captcha', $captchaText);
    }

    public function getComments(Request $request)
    {
        $cacheKey = 'comments_' . md5(json_encode($request->all())); 

        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return response()->json($data);
        }

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

        // store the data in the cache
        Cache::put($cacheKey, $data);

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


    /**
     * stores new comment
     * 
     * @param Request $request
     */
    public function storeComment(CommentRequest $request)
    {

        $user = User::firstOrCreate([
            'name' => $request->username,
            'homepage' => $request->homepage,
            'email' => $request->email,
        ]);

        $comment = Comment::create([
            'text' => parseHtmlTags($request->comment),
            'user_id' => $user->id,
            'comment_reply_id' => $request->replyId,
        ]);

        $this->clearCache();

        $commentId = $comment->id;

        $fileNames = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('/uploads'), $fileName);
                $fileNames[] = $fileName;
                CommentFile::create([
                    'comment_id' => $commentId,
                    'file_path' => $fileName,
                ]);
            }
        };

        NewRecordCreated::dispatch('new_comment_posted');

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

    public function clearCache()
    {
        Cache::flush();
    }
}
