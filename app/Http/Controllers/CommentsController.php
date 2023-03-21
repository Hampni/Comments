<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CommentFile;
use App\Models\User;
use App\Http\Requests\CommentRequest;
use App\Events\NewRecordCreated;

class CommentsController extends Controller
{

    private const COMMENTS_REPLIES_PER_PAGE = 3;
    private const COMMENTS_PER_PAGE = 5;

    /**
     * Retrieve comments based on the given request parameters
     * 
     *  @param Request $request The HTTP request object.
     *  @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved comments.
     */
    public function getComments(Request $request)
    {
        $requestParams = [
            'paginate' => self::COMMENTS_PER_PAGE,
            'page' => $request->input('page', '1'),
            'sort_by' => $request->input('sort_by', 'comments.created_at'),
            'order_by' => $request->input('order_by', 'desc'),
        ];

        $primeComments = Comment::getComments($requestParams);

        $data = [
            'comments' => $primeComments,
            'totalPages' => ceil($primeComments->total() / self::COMMENTS_PER_PAGE),
            'commentsAmount' => $primeComments->total(),
        ];

        return response()->json($data);
    }

    /**
     * Retrieve reply comments based on the given request parameters and parent comment ID.
     * 
     * @param Request $request The HTTP request object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved reply comments.
     */
    public function getReplyComments(Request $request)
    {
        $requestParams = [
            'paginate' => self::COMMENTS_REPLIES_PER_PAGE,
            'page' => $request->input('page', '1'),
        ];

        $replyComments = Comment::getComments($requestParams, $request->input('id'));

        $data = [
            'replyComments' => $replyComments,
            'totalPages' => ceil($replyComments->total() / self::COMMENTS_REPLIES_PER_PAGE),
        ];

        return response()->json($data);
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

        $fileNames = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('/uploads'), $fileName);
                $fileNames[] = $fileName;
                CommentFile::create([
                    'comment_id' => $comment->id,
                    'file_path' => $fileName,
                ]);
            }
        };

        return true;
    }
}
