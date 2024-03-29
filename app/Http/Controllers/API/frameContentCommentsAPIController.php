<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FrameContentComment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FrameContentCommentsAPIController extends Controller
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

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->only('frame_content_id', 'contact_id', 'content_comment'), [
                'frame_content_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
                'content_comment' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $post_id = 'frameContent_'. $request->frame_content_id;

            $input = $request->only('frame_content_id', 'contact_id', 'content_comment');
            // var_dump($input);
            $comment = FrameContentComment::create($input);
                $notification = Notification::create([
                'action' => 'comment',
                'user_id' => $request->contact_id,
                'contact_id' => $request->user_id,
                'post_id' => $post_id,
            ]);
            $data = [
                'comment' => $comment,
                'message' => 'Content comment successfully created'
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
            $validator = Validator::make($request->only('content_comment'), [
               'content_comment' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $comment = FrameContentComment::where('id', $id)->first();
            $comment->content_comment = $request->content_comment;
            $comment->save();

            return response()->json(['message' => 'Content Comment successfully updated', 'status' => true]);
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
        $comment = FrameContentComment::find($id);
        if ($comment) {
            $comment->delete();
            return response()->json(['message', 'Content Comment successfully deleted']);
        } else {
            return response()->json(['message', 'Content Comment not found']);
        }
    }

     /**
     * FrameContentsComments
     */

     public function frameContentComments ($id){

        try {
             $frameComments = DB::table('frame_content_comments')
            ->join('contacts', 'frame_content_comments.contact_id', 'contacts.id')
            ->join('users', 'frame_content_comments.contact_id', 'users.id')
            ->where('frame_content_comments.frame_content_id', $id)
            ->select('frame_content_comments.*', 'contacts.contact_firstname', 'contacts.contact_lastname', 'users.profil_picture')
            ->get();
            return response()->json([
                'frame_content_comments' => $frameComments,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);

        }
     }
}
