<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\FrameContent;
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

            $verif_souscription = DB::table('souscriptions')
                ->where('user_id', '=', $request->user_id)
                ->where('plan_id', '=', $request->plan_id)->first();
            // var_dump($verif_souscription); die;
            if (!$verif_souscription) {
                return response()->json(['error' => 'You have to suscribe to a plan first.']);
            }

            $verify_plan_frame = DB::table('frames')->where('frames.plan_id', '=', $request->plan_id)->first();
            if ($verify_plan_frame) {
                return response()->json(['error' => 'This frame is already linked to a plan']);
            }

            $input = $request->only('frame_title', 'frame_description', 'plan_id');
            $frame = Frame::create($input);

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
        $frame = Frame::find($id);
        if ($frame) {
            return response()->json(['Frame', $frame]);
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

            $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
            $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();

            $user = DB::table('souscriptions')
                ->join('users', 'souscriptions.user_id', 'users.id',)
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
            $souscription->user_id = $request->receiver_id;
            $souscription->save();

            if ($souscription) {
                return response()->json(['response' => 'Frame successfully transfered']);
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
            //code...
            $validator = Validator::make($request->only('frame_title', 'frame_description'), [
                'frame_title' => ['string'],
                'frame_description' => ['string'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $frame->update([
                'frame_title' => $request->frame_title,
                'frame_description' => $request->frame_description,
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
                ->join('users', 'souscriptions.user_id', 'users.id',)
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
        } catch (\Throwable) {
        }
    }
}
