<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FrameContent;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FrameContentsAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Contents from a frame
     */
    public function frame_contents($id)
    {
        $frame_contents = DB::table('frame_contents')->where('frame_id', '=', $id)->get();
        return response()->json(['frame_contents' => $frame_contents]);
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
            $validator = Validator::make($request->only('path', 'frame_id'), [
                'path' => 'required',
                'path.*' => 'file|mimes:m4v,avi,flv,mp4,mov,jpeg,jpg,png,gif,PNG,JPG,JPEG,GIF',
                'frame_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            if ($request->hasfile('path')) {
                $number_of_arriving_file = (count($request->file('path')));
                $number_of_existing_files = DB::table('frame_contents')->where('frame_id', '=', $request->frame_id)->count();

                $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
                $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();
                $user = DB::table('users')->where('users.id', '=', $plan->user_id)->first();

                $total = $number_of_arriving_file + $number_of_existing_files;
                $storage_capacity = (int)$plan->storage_capacity;

                $tab_extensions = ['m4v', 'avi', 'flv', 'mp4', 'mov'];
                if ($storage_capacity >= $total) {
                    // var_dump(count($request->file('path')));
                    // die;
                    foreach ($request->file('path') as $key => $file) {

                        $extension = explode('.', $file->getClientOriginalName())[1];
                        if (in_array($extension, $tab_extensions)) {
                            $number_of_video_content = DB::table('frame_contents')->where('content_type', '=', 'video')->where('frame_id', '=', $request->frame_id)->count();
                            if ($plan->plan_title == 'Free Trial') {
                                if ($number_of_video_content >= 1) {
                                   goto video_limit;
                                }
                            }

                            if ($plan->plan_title == 'Premium' && $plan->plan_type == 'Lite') {
                                // $number_of_video_content = DB::table('frame_contents')->where('content_type', '=', 'video')->where('frame_id', '=', $request->frame_id)->count();
                                if (($number_of_arriving_file + $number_of_video_content) > ($storage_capacity / 2)) {
                                    // return response()->json(['error' => 'You can not upload more than 50% of video contents in the limit of your frame storage capacity'], 400);
                                    goto video_limit;
                                }
                            }
                        }


                        $filename  = $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . (time() + $key) . '.' . $extension;

                        $input = $request->only('content_type', 'filepath', 'frame_id');

                        $path = 'Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $request->frame_id;

                        $input['filepath'] = $path . '/' . $filename;
                        if (in_array($extension, $tab_extensions)) {
                            $input['content_type'] = 'video';
                        }
                        $file->move(public_path($path), $filename);
                        $frame_content = FrameContent::create($input);
                    }
                    $data = [
                        'frame_content' => $frame_content,
                        'message' => 'Contents added successfully.'
                    ];
                    return response()->json($data, 200);

                    video_limit:
                    return response()->json(['error', 'youn can not upload more video than your limit']);
                } else {
                    return response()->json(['error' => 'You can not upload more files than your storage capacity']);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
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
        $frame_content = FrameContent::where('id', $id)->first();
        return response()->json(['frame_content' => $frame_content]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrameContent $frame_content)
    {
    }

    public function updateFrameContent(Request $request, $id)
    {

        try {
            if ($request->content_type == 'image') {
                // $folder_type = 'image_folder';
                $validator = Validator::make($request->only('content_type', 'path', 'frame_id'), [
                    'content_type' => 'required|string',
                    'path' => 'required',
                    'path' => 'image|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG,GIF|max:2048',
                    'frame_id' => 'required|numeric',
                ]);
            } else {
                // $folder_type = 'video_folder';
                $validator = Validator::make($request->only('content_type', 'path', 'frame_id'), [
                    'content_type' => 'required|string',
                    'path' => 'required',
                    'path' => 'mimes:m4v,avi,flv,mp4,mov',
                    'frame_id' =>  'required|numeric',
                ]);
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $frame_content = FrameContent::where('id', $id)->first();

            if ($request->hasfile('path')) {

                if (!is_null($frame_content->filepath)) {
                    unlink(public_path($frame_content->filepath));
                }
                $extension = explode('.', $request->path->getClientOriginalName())[1];

                $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
                $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();
                $user = DB::table('users')->where('users.id', '=', $plan->user_id)->first();

                $filename  = $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . time() . '.' . $extension;
                // die($filename);

                $input = $request->only('content_type', 'filepath', 'frame_id');

                // die($filename);
                $path = 'Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $request->frame_id;
                $input['filepath'] = $path . '/' . $filename;
                // var_dump($input);die;
                $request->path->move(public_path($path), $filename);
            }
            $frame_content->update($input);
            $data = [
                'frame_content' => $frame_content,
                'message' => 'Contents update successfully.'
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
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
        $frame_content = FrameContent::find($id);
        if ($frame_content) {
            unlink(public_path($frame_content->filepath));
            $frame_content->delete();
            return response()->json(['message', 'Frame contents successfully deleted']);
        } else {
            return response()->json(['message', 'Frame contents not found']);
        }
    }

    public function countFiles($path)
    {
        $files = File::files($path);
        $countFiles = 0;

        if ($files !== false) {
            $countFiles = count($files);
        }

        return $countFiles;
    }
}
