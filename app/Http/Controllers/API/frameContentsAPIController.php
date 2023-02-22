<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FrameContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class frameContentsAPIController extends Controller
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
        $user = Auth::user();
        // dd($user);
        $validator = Validator::make($request->only('content_type', 'path','frame_id'), [
            'content_type' => ['required', 'string'],
            'path' => ['required', 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'],
            'frame_id' => ['required', 'numeric'],
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $filename  = $request->name . '_' . time() . '.' . $request->path->extension();

        $input = $request->only('content_type', 'filepath','frame_id');
        if ($request->content_type == 'image') {
            $folder_type = 'image_folder';
        }else{
            $folder_type = 'video_folder';
        }

        $path = public_path('Users_frames/'. $user .'frame_'. $request->frame_id . '/' . $folder_type . '/'. $filename);
        $input['filepath'] = $path;
        // var_dump($input);die;
        $request->path->move(public_path('Users_frames/frame_'. $request->frame_id . '/' . $folder_type . '/'), $filename);
        $frame_content = FrameContent::create($input);

        $data = [
            'frame_content' => $frame_content,
            'message' => 'Contents added successfully.'
        ];

        return response()->json($data, 200);
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
        $frame_content = FrameContent::find($id);
        if ($frame_content) {
            $frame_content->delete();
            return response()->json(['message','Frame contents successfully deleted']);
        }else{
            return response()->json(['message','Frame contents not found']);
        }
    }
}
