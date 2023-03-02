<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FrameContentTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FrameContentTagsAPIController extends Controller
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

            $validator = Validator::make($request->only('frame_content_id', 'contact_id'), [
                'frame_content_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $input = $request->only('frame_content_id', 'contact_id');
            // var_dump($input);
            $tag = FrameContentTag::create($input);

            $data = [
                'tag' => $tag,
                'message' => 'Contact successfully tagged on content'
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
        $tag = FrameContentTag::find($id);
        if ($tag) {
            $tag->delete();
            return response()->json(['message', 'Contact successfully deleted from content tags']);
        } else {
            return response()->json(['message', 'Contact not found on content tags']);
        }
    }
}
