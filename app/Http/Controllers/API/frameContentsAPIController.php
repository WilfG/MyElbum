<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\FrameBin;
use App\Models\FrameContent;
use App\Models\FrameContentComment;
use App\Models\FrameContentTag;
use Carbon\Carbon;
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
            $validator = Validator::make($request->only('path', 'frame_id', 'user_id',  'pan', 'zoom', 'height', 'width', 'position', 'size'), [
                'path' => 'required',
                'path.*' => 'file|mimes:m4v,avi,flv,mp4,mov,jpeg,jpg,png,gif,PNG,JPG,JPEG,GIF',
                'frame_id' => ['required', 'numeric'],
                'user_id' => ['required', 'numeric'],
                'pan.*' => ['required', 'string'],
                'zoom.*' => ['required', 'string'],
                'height.*' => ['required', 'string'],
                'width.*' => ['required', 'string'],
                'position.*' => ['required', 'string'],
                'size.*' => ['required', 'string'],
            ]);
            // var_dump();die;
            if ($validator->fails())
                return response()->json($validator->errors(), 400);
            // var_dump($request->pan);die;
            if ($request->hasfile('path')) {
                $number_of_arriving_file = (count($request->file('path')));
                $number_of_existing_files = DB::table('frame_contents')->where('frame_id', '=', $request->frame_id)->count();

                $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
                $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();
                // $user = DB::table('users')->where('id', '=', $request->user_id)->first();
                $user = DB::table('souscriptions')
                    ->join('users', 'souscriptions.user_id', 'users.id')
                    ->where('souscriptions.user_id', '=', $request->user_id)
                    ->where('souscriptions.plan_id', '=', $plan->id)
                    ->select('users.*')->first();

                if (!$user) {
                    return response()->json(['error' => 'you can not add content to this frame because your are not the owner.']);
                }


                $pan = explode(';', $request->pan);
                $zoom = explode(';', $request->zoom);
                $height = explode(';', $request->height);
                $width = explode(';', $request->width);
                $position = explode(';', $request->position);
                $size = explode(';', $request->size);
                $total = $number_of_arriving_file + $number_of_existing_files;
                $storage_capacity = (int)$plan->storage_capacity;

                $tab_extensions = ['m4v', 'avi', 'flv', 'mp4', 'mov'];
                if ($storage_capacity >= $total) {
                    $number_of_arriving_video_file = 0;
                    foreach ($request->file('path') as $key => $file) {
                        $ext = explode('.', $file->getClientOriginalName())[1];
                        if (in_array($ext, $tab_extensions)) {
                            $number_of_arriving_video_file += 1;
                        }
                    }

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
                                $half_storage_capacity = $storage_capacity / 2;
                                $total_video_content = $number_of_video_content + $number_of_arriving_video_file;
                                if ($half_storage_capacity < $total_video_content) {
                                    // var_dump($total_video_content); die;
                                    goto video_limit;
                                }
                            }
                        }


                        $filename  = $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . (time() + $key) . '.' . $extension;

                        $input = $request->only('content_type', 'filepath', 'frame_id', 'pan', 'zoom', 'height', 'width', 'position', 'size');

                        $path = 'Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $request->frame_id;

                        $input['filepath'] = $path . '/' . $filename;
                        $input['pan'] = $pan[$key];
                        $input['zoom'] = $zoom[$key];
                        $input['height'] = $height[$key];
                        $input['width'] = $width[$key];
                        $input['position'] = $position[$key];
                        $input['size'] = $size[$key];
                        if (in_array($extension, $tab_extensions)) {
                            $input['content_type'] = 'video';
                        }
                        $file->move(public_path($path), $filename);
                        $frame_content = FrameContent::create($input);
                    }
                    $frame_to_live = Frame::where('id', $frame_content->frame_id)->first();
                    $frame_to_live->frame_status = 'live';
                    $frame_to_live->save();

                    $data = [
                        'frame_content' => $frame_content,
                        'message' => 'Contents added successfully.'
                    ];
                    return response()->json($data, 200);

                    video_limit:
                    return response()->json(['error' => 'youn can not upload more video than your limit']);
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
            $validator = Validator::make($request->only('path', 'frame_id', 'user_id',  'pan', 'zoom', 'height', 'width', 'position', 'size'), [
                'path' => 'required',
                'path' => 'file|mimes:m4v,avi,flv,mp4,mov,jpeg,jpg,png,gif,PNG,JPG,JPEG,GIF',
                'frame_id' => 'required|numeric',
                'user_id' => ['required', 'numeric'],
                'pan' => ['required', 'numeric'],
                'zoom' => ['required', 'numeric'],
                'height' => ['required', 'numeric'],
                'width' => ['required', 'numeric'],
                'position' => ['required', 'numeric'],
                'size' => ['required', 'numeric'],
            ]);


            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
            $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();
            // die($request->path);die;
            if ($request->hasfile('path')) {
                $tab_extensions = ['m4v', 'avi', 'flv', 'mp4', 'mov'];
                $extension = explode('.', $request->path->getClientOriginalName())[1];

                $content_to_update = DB::table('frame_contents')
                    ->where('id', '=', $id)->first();
                // die($content_to_update);
                if ($content_to_update->content_type == 'image') {
                    if (in_array($extension, $tab_extensions)) {
                        $number_of_video_content = DB::table('frame_contents')->where('content_type', '=', 'video')->where('frame_id', '=', $request->frame_id)->count();

                        if ($plan->plan_title == 'Free Trial') {
                            if ($number_of_video_content >= 1) {
                                return response()->json(['error' => 'You cannot add more video in this plan, you can update image by image.']);
                            }
                        }

                        if ($plan->plan_title == 'Premium' && $plan->plan_type == 'Lite') {
                            $storage_capacity = (int)$plan->storage_capacity;
                            $half_storage_capacity = $storage_capacity / 2;
                            $total_video_content = $number_of_video_content + 1;

                            if ($half_storage_capacity < $total_video_content) {
                                return response()->json(['error' => 'You cannot add more video in this plan, you can update image by image.']);
                            }
                        }
                    }
                }
                if (!is_null($content_to_update->filepath)) {
                    unlink(public_path($content_to_update->filepath));
                    $frame_content_comment = FrameContentComment::where('id', $request->frame_content_id);
                    $frame_content_comment->delete();
                    $frame_content_tag = FrameContentTag::where('id', $request->frame_content_id);
                    $frame_content_tag->delete();
                    $content = DB::table('frame_contents')->where('id', '=', $id);
                    $content->delete();
                }


                $user = DB::table('users')->where('users.id', '=', $request->user_id)->first();

                $filename  = $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . time() . '.' . $extension;
                $path = 'Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $request->frame_id;

                $input = $request->only('filepath', 'frame_id',  'pan', 'zoom', 'height', 'width', 'position', 'size');

                $input['filepath'] = $path . '/' . $filename;

                if (in_array($extension, $tab_extensions)) {
                    $input['content_type'] = 'video';
                }

                $request->path->move(public_path($path), $filename);
                $frame_content = FrameContent::create($input);
                $data = [
                    'frame_content' => $frame_content,
                    'message' => 'Contents update successfully.'
                ];
                return response()->json($data, 200);
            } else {
                response()->json(['error' => 'Upload a file please..']);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * AND add resource in bin
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id'), [
                'user_id' => ['required', 'numeric'],
            ]);
            // die($id);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $frame_content = FrameContent::find($id);
            if ($frame_content) {
                $frame_content->content_status = 'bin';

                // var_dump($request);die;
                $frame = DB::table('frames')->where('frames.id', '=', $frame_content->frame_id)->first();
                $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();

                $user = DB::table('souscriptions')
                    ->join('users', 'souscriptions.user_id', 'users.id')
                    ->where('souscriptions.user_id', '=', $request->user_id)
                    ->where('souscriptions.plan_id', '=', $plan->id)
                    ->select('users.*')->first();

                if (!$user) {
                    return response()->json(['error' => 'you can not delete this frame content because your are not the owner.']);
                }

                $extension = explode('.', $frame_content->filepath)[1];
                $path = public_path($frame_content->filepath);
                $frame_content->filepath = 'Users_bins/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id . '/' . $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . time() . '.' . $extension;

                if (!file_exists(public_path('Users_bins/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id))) {
                    mkdir(public_path('Users_bins/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id), 0777, true);
                }
                rename($path, public_path($frame_content->filepath));

                // unlink(public_path($frame_content->filepath));
                $frame_content->save();
                $check_bin = DB::table('frame_bins')
                    ->where('frame_id', '=', $frame->id)
                    ->where('frame_content_id', '=', $id)->first();

                // var_dump($check_bin->delete_date);die;
                if ($check_bin) {
                    return response()->json(['response' => 'Frame content already in bin']);
                }

                $bin = FrameBin::create([
                    'frame_id' => $frame->id,
                    'frame_content_id' => $id,
                    'delete_date' => Carbon::now()->addDays(30),
                ]);
                if ($bin) {
                    return response()->json(['response' => 'Frame contents successfully deleted']);
                }
            } else {
                return response()->json(['message', 'Frame contents not found']);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function empty_bin()
    {
        $frame_bins = FrameBin::all();
        foreach ($frame_bins as $key => $bin) {
            if (Carbon::now()->gt($bin->delete_date)) {
                $content_id = $bin->frame_content_id;
                // die($content_id); break;
                $bin->delete();
                $frame_content = FrameContent::where('id', $content_id)->where('content_status', 'bin')->first();
                if ($frame_content) {
                    unlink(public_path($frame_content->filepath));
                    return $frame_content->delete();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * AND add resource in bin
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore_frame($id, Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id'), [
                'user_id' => ['required', 'numeric'],
            ]);
            // die($id);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $frame_content = FrameContent::find($id);
            if ($frame_content) {
                $frame_content->content_status = 'live';

                // var_dump($request);die;
                $frame = DB::table('frames')->where('frames.id', '=', $frame_content->frame_id)->first();
                $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();

                $user = DB::table('souscriptions')
                    ->join('users', 'souscriptions.user_id', 'users.id')
                    ->where('souscriptions.user_id', '=', $request->user_id)
                    ->where('souscriptions.plan_id', '=', $plan->id)
                    ->select('users.*')->first();

                if (!$user) {
                    return response()->json(['error' => 'you can not delete this frame content because your are not the owner.']);
                }

                $extension = explode('.', $frame_content->filepath)[1];
                $path = public_path($frame_content->filepath);
                $frame_content->filepath = 'Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id . '/' . $user->firstname . '_' . $user->lastname . '_' . $frame->frame_title . '_' . date('Ymd') . '_' . time() . '.' . $extension;

                if (!file_exists(public_path('Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id))) {
                    mkdir(public_path('Users_frames/' . $user->firstname . '_' . $user->lastname . '/frame_' . $frame_content->frame_id), 0777, true);
                }
                rename($path, public_path($frame_content->filepath));

                // unlink(public_path($frame_content->filepath));
                $frame_content->save();
                $bin = FrameBin::where('frame_id', $frame->id)->where('frame_content_id', $id);
                if ($bin) {
                    $bin->delete();
                    return response()->json(['response' => 'Frame contents successfully restored']);
                }
            } else {
                return response()->json(['message', 'Frame contents not found']);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
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

    public function countVideoFiles($path)
    {
        $files = File::files($path);
        $countVideoFiles = 0;

        if ($files !== false) {
            $countVideoFiles = $files;
        }

        return $countVideoFiles;
    }
}
