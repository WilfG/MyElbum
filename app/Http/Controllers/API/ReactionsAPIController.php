<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReactionsAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reactions = DB::table('reactions')->get();
        return response()->json(['reactions' => $reactions]);
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
            if ($request->type == 'view') {
                $validator = Validator::make($request->only('type', 'user_id', 'contact_id', 'frame_id', 'frame_content_id'), [
                    'type' => ['required', 'string'],
                    'user_id' => ['required', 'string'],
                    'contact_id' => ['required', 'string'],
                    'frame_id' => ['nullable', 'string'],
                    'frame_content_id' => ['nullable', 'string'],
                ]);

                if ($request->frame_id) {
                    $post_id =  'frame_' . $request->frame_id;
                }

                if ($request->frame_content_id) {
                    $post_id =  'frameContent_' . $request->frame_content_id;
                }
            } else {
                $validator = Validator::make($request->only('type', 'user_id', 'contact_id', 'frame_id', 'frame_content_id', 'comment_id', 'content_comment_id'), [
                    'type' => ['required', 'string'],
                    'user_id' => ['required', 'string'],
                    'contact_id' => ['required', 'string'],
                    'frame_id' => ['nullable', 'string'],
                    'frame_content_id' => ['nullable', 'string'],
                    'comment_id' => ['nullable', 'string'],
                    'content_comment_id' => ['nullable', 'string'],
                ]);

                if ($request->frame_id) {
                    $post_id =  'frame_' . $request->frame_id;
                }
                if ($request->frame_content_id) {
                    $post_id =  'frameContent_' . $request->frame_content_id;
                }
                if ($request->comment_id) {
                    $post_id =  'frameComment_' . $request->comment_id;
                }
                if ($request->content_comment_id) {
                    $post_id =  'contentComment_' . $request->content_comment_id;
                }
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $input = $request->only('type', 'user_id', 'contact_id', 'frame_id', 'frame_content_id', 'comment_id', 'content_comment_id');

            $reaction_verif = DB::table('reactions')
                ->where('user_id', '=', $request->user_id)
                ->where('type', '=', $request->type)
                ->where('contact_id', '=', $request->contact_id)
                ->where(function ($query) use ($request) {
                    $query->orWhere('frame_id', $request->frame_id)
                        ->orWhere('frame_content_id', $request->frame_content_id)
                        ->orWhere('comment_id', $request->comment_id)
                        ->orWhere('content_comment_id', $request->content_comment_id);
                })->first();

            if (!isset($reaction_verif->frame_id) && !isset($reaction_verif->frame_content_id) && !isset($reaction_verif->comment_id) && !isset($reaction_verif->content_comment_id)) {
                // var_dump($reaction_verif);die;
                if ($reaction_verif->type == 'like') {
                    if (isset($request->frame_id) && $reaction_verif->frame_id == $request->frame_id) {
                        $reaction = Reaction::where('id', $reaction_verif->id)
                            ->where('user_id', $request->user_id)
                            ->where('frame_id', $request->frame_id)->first();
                        $reaction->delete();
                    }
                    if (isset($request->frame_content_id) && $reaction_verif->frame_content_id == $request->frame_content_id) {
                        $reaction = Reaction::where('id', $reaction_verif->id)
                            ->where('user_id', $request->user_id)
                            ->where('frame_content_id', $request->frame_content_id)->first();
                        $reaction->delete();
                    }
                    if (isset($request->comment_id) && $reaction_verif->comment_id == $request->comment_id) {
                        // var_dump($reaction_verif->comment_id);die;
                        $reaction = Reaction::where('id', $reaction_verif->id)
                            ->where('user_id', $request->user_id)
                            ->where('comment_id', $request->frame_id)->first();
                        $reaction->delete();
                    }
                    if (isset($request->content_comment_id) && $reaction_verif->content_comment_id == $request->content_comment_id) {
                        $reaction = Reaction::where('id', $reaction_verif->id)
                            ->where('user_id', $request->user_id)
                            ->where('content_comment_id', $request->content_comment_id)->first();
                        $reaction->delete();
                    }

                    return response()->json(['message' => 'Unliked']);
                } else {
                    return response()->json(['message' => 'Already viewed']);
                }
            } else {
                $verif_again = DB::table('reactions')
                    ->where('type', '=', $request->type)
                    ->where(function ($query) use ($request) {
                        $query->orWhere('frame_id', $request->frame_id)
                            ->orWhere('frame_content_id', $request->frame_content_id)
                            ->orWhere('comment_id', $request->comment_id)
                            ->orWhere('content_comment_id', $request->content_comment_id);
                    })->first();
                    
                if (!$verif_again) {
                    $reaction = Reaction::create($input);
                    $notification = Notification::create([
                        'action' => $request->type,
                        'user_id' => $request->user_id,
                        'contact_id' => $request->contact_id,
                        'post_id' => $post_id,
                    ]);
                }else {
                    if ($verif_again->type == 'like') {

                    }
                }
                $data = [
                    'notification' => $notification,
                    'reaction' => $reaction,
                    'message' => 'liked'
                ];
                return response()->json($data, 200);
            }
        } catch (\Throwable $th) {
            response()->json(['message' => $th->getMessage()]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function reactionByFrame($id)
    {
        $reactions =  DB::table('reactions')
            ->join('contacts', 'reactions.contact_id', 'contacts.id')
            ->where('reactions.frame_id', $id)
            ->get();
        return response()->json(['reactions' => $reactions]);
    }

    public function reactionByFrameContent($id)
    {
        $reactions =  DB::table('reactions')
            ->join('contacts', 'reactions.contact_id', 'contacts.id')
            ->where('reactions.frame_content_id', $id)
            ->get();
        return response()->json(['reactions' => $reactions]);
    }

    public function reactionBycomment($id)
    {
        $likes =  DB::table('reactions')
            ->join('contacts', 'reactions.contact_id', 'contacts.id')
            ->where('reactions.comment_id', $id)
            ->get();
        return response()->json(['likes' => $likes]);
    }

    public function reactionByFrameContentComment($id)
    {
        $likes =  DB::table('reactions')
            ->join('contacts', 'reactions.contact_id', 'contacts.id')
            ->where('reactions.content_comment_id', $id)
            ->get();
        return response()->json(['likes' => $likes]);
    }
}
