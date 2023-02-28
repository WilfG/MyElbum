<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $validator = Validator::make($request->only('frame_title', 'frame_description', 'plan_id'), [
                'frame_title' => ['required', 'string'],
                'frame_description' => ['required', 'string'],
                'plan_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
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
        $frame = Frame::find($id);
        if ($frame) {
            $frame->delete();
            return response()->json(['message', 'Frame successfully deleted']);
        } else {
            return response()->json(['message', 'Frame not found']);
        }
    }
}
