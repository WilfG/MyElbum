<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentsAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->only('frame_id', 'contact_id', 'comment_description', 'user_id'), [
                'frame_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
                'comment_description' => ['required', 'string'],
                'user_id' => ['required', 'numeric'],

            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $post_id = 'frame_'. $request->frame_id;
            $input = $request->only('frame_id', 'contact_id', 'comment_description');
            // var_dump($input);
            $comment = Comment::create($input);
            $notification = Notification::create([
                'action' => 'comment',
                'user_id' => $request->user_id,
                'contact_id' => $request->contact_id,
                'post_id' => $post_id,
            ]);

            $data = [
                'comment' => $comment,
                'notification' => $notification,
                'message' => 'Comment successfully created'
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->only('comment_description'), [
               'comment_description' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $comment = Comment::where('id', $id)->first();
            $comment->comment_description = $request->comment_description;
            $comment->save();

            return response()->json(['message' => 'Comment successfully updated', 'status' => true]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 400]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if ($comment) {
            $comment->delete();
            return response()->json(['message', 'Comment successfully deleted']);
        } else {
            return response()->json(['message', 'Comment not found']);
        }
    }

    
    /**
     * FrameComments
     */

     public function frameComments ($id){

        try {
            $frameComments = DB::table('comments')
            ->join('contacts', 'comments.contact_id', 'contacts.id')
            ->join('users', 'comments.contact_id', 'users.id')
            ->where('comments.frame_id', $id)
            ->select('comments.*', 'contacts.contact_firstname', 'contacts.contact_lastname', 'users.profil_picture')
            ->get();
            return response()->json([
                'frame_comments' => $frameComments,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);

        }
     }
     
}
