<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagsAPIController extends Controller
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

            $validator = Validator::make($request->only('frame_id', 'contact_id', 'user_id'), [
                'frame_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
                'user_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $post_id = 'frame_'. $request->frame_id;


            $verify_tag = DB::table('tags')
                ->where('tags.frame_id', '=', $request->frame_id)
                ->where('tags.contact_id', '=', $request->contact_id)->first();


            if ($verify_tag) {
                return  response()->json(['error' => 'You already tag this contact']);
            }

            $input = $request->only('frame_id', 'contact_id');
            // var_dump($input);
            $tag = Tag::create($input);
            $notification = Notification::create([
                'action' => 'tag',
                'user_id' => $request->user_id,
                'contact_id' => $request->contact_id,
                'post_id' => $post_id,
            ]);
            $data = [
                'tag' => $tag,
                'notifications' => $notification,
                'message' => 'Tag successfully created'

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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->delete();
            return response()->json(['message' => 'Contact successfully deleted from tags']);
        } else {
            return response()->json(['message' => 'Contact not found on tags']);
        }
    }

    public function usersTaggedOnFrame($id)
    {
        $users = DB::table('contacts')
            ->join('tags', 'contacts.id', 'tags.contact_id')
            ->where('tags.frame_id', '=', $id)
            ->select('contacts.*')->get();

        return response()->json(['users_tagged' => $users]);
    }

    public function framesWhereUserIstagged($id){
        try {
            $frames = DB::table(('frames'))
            ->join('tags', 'tags.frame_id', 'frames.id')
            ->where('tags.contact_id', $id)
            ->select('frames.*')->get();

            if ($frames) {
                return response()->json(['frames' => $frames]);
            }

            return response()->json(['message' => 'you are not tagged on any frame yet']);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }

    }
}
