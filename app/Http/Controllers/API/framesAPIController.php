<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\FrameContent;
use App\Models\Notification;
use App\Models\Souscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class FramesAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $frames = DB::table('frames')->get();
        // ->where('user_id', Auth::id())->get(); // à améliorer
        return response()->json(['frames' => $frames]);
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
            $validator = Validator::make($request->only('frame_title', 'frame_description', 'plan_id', 'user_id'), [
                'frame_title' => ['required', 'string'],
                'frame_description' => ['required', 'string'],
                'plan_id' => ['required', 'numeric'],
                'user_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $verif_souscription = DB::table('souscriptions')// verify if the current user have already suscribed to the current plan
                ->where('user_id', '=', $request->user_id)
                ->where('plan_id', '=', $request->plan_id)->first();
            // var_dump($verif_souscription); die;
            if (!$verif_souscription) {
                return response()->json(['error' => 'You have to suscribe to this plan first.']);
            }

            $verify_frame_title = DB::table('frames')->where('frame_title', $request->frame_title)->first();
            if ($verify_frame_title) {
                    return response()->json(['error' => "This frame's title is already in used."]);
            }
            
            $shareability_code = rand(10000, 99999);
            $input = $request->only('frame_title', 'frame_description', 'plan_id');
            $input['shareability_code'] = $shareability_code;
            // var_dump($input);die;
            $frame = Frame::create($input);
            
            $souscription = Souscription::where('user_id', '=', $request->user_id)
            ->where('plan_id', '=', $request->plan_id)->first();
            $souscription->frame_id = $verif_souscription->frame_id . ','. $frame->id;
            $souscription->save();

            $data = [
                'frame' => $frame,
                'message' => 'Frame successfully created'
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
        $frame = DB::table('frames')
            ->where('frames.id', '=', $id)
            ->join('souscriptions', 'frames.plan_id', '=', 'souscriptions.plan_id')
            ->join('users', 'souscriptions.user_id', '=', 'users.id')
            ->select('frames.*', 'users.firstname', 'users.lastname', 'users.profil_picture')
            ->first();

        if ($frame) {
            $contents = DB::table('frame_contents')->where('frame_contents.frame_id', $frame->id)->get();
            $comments = DB::table('comments')->where('frame_id', $frame->id)->join('users', 'comments.contact_id', 'users.id')->select('comments.*', 'users.profil_picture')->get();
            $tags = DB::table('tags')->where('frame_id', $frame->id)->get();
            $reactions = DB::table('reactions')->where('frame_id', $frame->id)->get();
            $plan = DB::table('plans')->where('id', $frame->plan_id)->first();
            $frame->user_id = $id;
            $frame->plan = $plan;
            $frame->contents = $contents;
            $frame->comments = $comments;
            $frame->tags = $tags;
            $frame->reactions = $reactions;

            foreach ($frame->contents as $content) {
                $content_comments = DB::table('frame_content_comments')->where('frame_content_id', $content->id)->get();
                $content_tags = DB::table('frame_content_tags')->where('frame_content_id', $content->id)->get();
                $content_reactions = DB::table('reactions')->where('frame_content_id', $content->id)->get();
                $content->content_comments = $content_comments;
                $content->content_tags = $content_tags;
                $content->content_reactions = $content_reactions;
            }

            return response()->json(['Frame' => $frame]);
        } else {
            return response()->json(['message' => 'Frame not found']);
        }
    }

    /**
     * Verify if your password before frame transfer 
     */
    public function frame_transfert_verif(Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id', 'password'), [
                'user_id' => ['string', 'required'],
                'password' => ['string', 'required'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = DB::table('users')->where('id', '=', $request->user_id)->first();
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['response' => true]);
            } else {
                return response()->json(['response' => false]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function transfer_frame(Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id', 'receiver_id', 'frame_id'), [
                'user_id' => ['string', 'required'],
                'receiver_id' => ['string', 'required'],
                'frame_id' => ['string', 'required'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $post_id = 'frame_' . $request->frame_id;

            $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
            $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();

            $user = DB::table('souscriptions')
                ->join('users', 'souscriptions.user_id', 'users.id')
                ->where('souscriptions.user_id', '=', $request->user_id)
                ->where('souscriptions.plan_id', '=', $plan->id)
                ->select('users.*')->first();

            if (!$user) {
                return response()->json(['error' => 'you can not transfer this frame because your are not the owner.']);
            }

            $receiver = User::where('id', $request->receiver_id)->first();
            $frame_contents = FrameContent::where('frame_id', $request->frame_id)->get();
            // var_dump($frame_contents);die;

            foreach ($frame_contents as $key => $content) {
                $extension = explode('.', $content->filepath)[1];
                $path = public_path($content->filepath);
                $content->filepath = 'Users_frames/' . $receiver->firstname . '_' . $receiver->lastname . '/frame_' . $request->frame_id . '/' . $receiver->firstname . '_' . $receiver->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . time() . '.' . $extension;
                if (!file_exists(public_path('Users_frames/' . $receiver->firstname . '_' . $receiver->lastname . '/frame_' . $request->frame_id))) {
                    mkdir(public_path('Users_frames/' . $receiver->firstname . '_' . $receiver->lastname . '/frame_' . $request->frame_id), 0777, true);
                }
                rename($path, public_path($content->filepath));
                $content->save();
            }
            // die('ok');
            $souscription = Souscription::where('souscriptions.user_id', $request->user_id)
                ->where('souscriptions.plan_id', $plan->id)->first();

            if ($souscription) {
                $souscription->user_id = $request->receiver_id;
                $souscription->save();
                $notification = Notification::create([
                    'action' => 'transfer',
                    'user_id' => $request->user_id,
                    'contact_id' => $request->receiver_id,
                    'post_id' => $post_id,
                ]);
                return response()->json([
                    'message' => 'Frame successfully transfered',
                    ''
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage(), 500]);
        }
    }



    /**
     * Display the specified user(id).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userFrame($id)
    {
        $frames = DB::table('frames')
            // ->join('plans', 'frames.plan_id', '=', 'plans.id')
            ->join('souscriptions', 'frames.plan_id', '=', 'souscriptions.plan_id')
            ->join('users', 'souscriptions.user_id', '=', 'users.id')
            ->where('souscriptions.user_id', '=', $id)
            ->select('frames.*', 'frames.plan_id')->get();

        foreach ($frames as $frame) {
            $contents = DB::table('frame_contents')->where('frame_contents.frame_id', $frame->id)->get();
            $comments = DB::table('comments')->where('frame_id', $frame->id)
                ->join('users', 'comments.contact_id', 'users.id')
                ->select('comments.*', 'users.profil_picture')
                ->get();
            $tags = DB::table('tags')->where('frame_id', $frame->id)->get();
            $reactions = DB::table('reactions')->where('frame_id', $frame->id)->get();
            $plan = DB::table('plans')->where('id', $frame->plan_id)->first();
            $frame->user_id = $id;
            $frame->plan = $plan;
            $frame->contents = $contents;
            $frame->comments = $comments;
            $frame->tags = $tags;
            $frame->reactions = $reactions;

            foreach ($frame->contents as $content) {
                $content_comments = DB::table('frame_content_comments')->where('frame_content_id', $content->id)->get();
                $content_tags = DB::table('frame_content_tags')->where('frame_content_id', $content->id)->get();
                $content_reactions = DB::table('reactions')->where('frame_content_id', $content->id)->get();
                $content->content_comments = $content_comments;
                $content->content_tags = $content_tags;
                $content->content_reactions = $content_reactions;
            }
        }

        if ($frames) {
            return response()->json(['frames' => $frames]);
        } else {
            return response()->json(['message' => 'Frames not found']);
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Frame $frame)
    {
        try {
            if (($request->visibility == 'MyContacts_Except')) {
                $validator = Validator::make($request->only('frame_title', 'frame_description', 'shareability','visibility', 'visibility_except_ids', 'canCommentReact', 'canCommentReact_except_ids'), [
                    'frame_title' => ['string'],
                    'frame_description' => ['string'],
                    'shareability' => ['numeric', 'required', 'max:1'],
                    // 'shareability_code' => ['numeric', 'nullable'],
                    'visibility' => ['string', 'nullable'],
                    'visibility_except_ids' => ['string', 'required'],
                    'canCommentReact' => ['string', 'nullable'],
                    'canCommentReact_except_ids' => ['string', 'nullable'],
                ]);
            }

            if (($request->canCommentReact == 'MyContacts_Except')) {
                $validator = Validator::make($request->only('frame_title', 'frame_description', 'shareability','visibility', 'visibility_except_ids', 'canCommentReact', 'canCommentReact_except_ids'), [
                    'frame_title' => ['string'],
                    'frame_description' => ['string'],
                    'shareability' => ['numeric', 'required', 'max:1'],
                    // 'shareability_code' => ['numeric', 'nullable'],
                    'visibility' => ['string', 'nullable'],
                    'visibility_except_ids' => ['string', 'nullable'],
                    'canCommentReact' => ['string', 'nullable'],
                    'canCommentReact_except_ids' => ['string', 'required'],
                ]);
            }

            if ($request->frame_title) {
                $validator = Validator::make($request->only('frame_title', 'frame_description', 'shareability','visibility', 'visibility_except_ids', 'canCommentReact', 'canCommentReact_except_ids'), [
                    'frame_title' => ['string'],
                    'frame_description' => ['string'],
                    'shareability' => ['numeric', 'required', 'max:1'],
                    // 'shareability_code' => ['numeric', 'nullable'],
                    'visibility' => ['string', 'nullable'],
                    // 'visibility_except_ids' => ['string', 'nullable'],
                    'canCommentReact' => ['string', 'nullable'],
                    // 'canCommentReact_except_ids' => ['string', 'required'],
                ]);
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if ($request->shareability) {
                $shareability_code = rand(10000, 99999);
            }else{
                $shareability_code = $frame->shareability_code;
            }

            if ($request->visibility) {
                $visibility_except_ids = $request->visibility_except_ids;
            }else{
                $visibility_except_ids = $frame->visibility_except_ids;
            }
           
            if ($request->canCommentReact) {
                $canCommentReact_except_ids = $request->canCommentReact_except_ids;
            }else{
                $canCommentReact_except_ids = $frame->canCommentReact_except_ids;
            }
            
            // var_dump($shareability_code);die;


            $frame->update([
                'frame_title' => $request->frame_title,
                'frame_description' => $request->frame_description,
                'shareability' => $request->shareability,
                'shareability_code' => $shareability_code,
                'visibility' => $request->visibility,
                'visibility_except_ids' => $visibility_except_ids,
                'canCommentReact' => $request->canCommentReact,
                'canCommentReact_except_ids' => $canCommentReact_except_ids,
            ]);

            return response()->json([
                'frame' => $frame,
                'message' => 'Frame successfully updated'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
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
        $frame = Frame::find($id);
        if ($frame) {
            $frame->delete();
            return response()->json(['message', 'Frame successfully deleted']);
        } else {
            return response()->json(['message', 'Frame not found']);
        }
    }

    /**
     * Reset a single frame to 0 content
     */
    public function frame_reset(Request $request)
    {

        try {
            $validator = Validator::make($request->only('user_id', 'frame_id'), [
                'user_id' => ['string', 'required'],
                'frame_id' => ['string', 'required'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            // var_dump($request);die;
            $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
            $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();

            $user = DB::table('souscriptions')
                ->join('users', 'souscriptions.user_id', 'users.id')
                ->where('souscriptions.user_id', '=', $request->user_id)
                ->where('souscriptions.plan_id', '=', $plan->id)
                ->select('users.*')->first();

            if (!$user) {
                return response()->json(['error' => 'you can not reset this frame because your are not the owner.']);
            }

            $frame_contents = FrameContent::where('frame_id', $request->frame_id)->get();

            foreach ($frame_contents as $key => $content) {
                unlink(public_path($content->filepath));
            }
            // var_dump($frame_contents); die;

            $frame_contents = FrameContent::where('frame_id', $request->frame_id);

            if ($frame_contents->delete()) {
                return response()->json(['response' => 'Frame successfully reset']);
            } else {
                return response()->json(['error' => 'Error when resetting a frame']);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function add_thumbnail_to_frame(Request $request)
    {
        try {
            $validator = Validator::make($request->only('path', 'frame_id'), [
                'thumbnail' => 'required',
                'thumbnail' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG,GIF',
                'frame_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $extension = explode('.', $request->thumbnail->getClientOriginalName())[1];
            $filename  = 'frame_' . $request->frame_id . '_' . date('Ymd') . '_' . time() . '.' . $extension;
            $path = 'Frame_thumbnails/frame_' . $request->frame_id;
            // die($path);


            $frame = Frame::find($request->frame_id);
            if ($frame->thumbnail) {
                unlink(public_path($frame->thumbnail));
            }
            $frame->thumbnail = $path . '/' . $filename;
            $request->file('thumbnail')->move(public_path($path), $filename);

            if ($frame->save()) {
                return response()->json(['message' => 'Thumbnail successfully added to frame']);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
