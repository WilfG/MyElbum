<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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

            $validator = Validator::make($request->only('frame_id', 'contact_id'), [
                'frame_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }


            $frame = DB::table('frames')->where('frames.id', '=', $request->frame_id)->first();
            $plan = DB::table('plans')->where('plans.id', '=', $frame->plan_id)->first();
            $user = DB::table('users')->where('users.id', '=', $plan->user_id)->first();
            $contact = DB::table('contacts')->where('id', '=', $request->contact_id)->first();

            $verify_tag = DB::table('tags')
                ->where('tags.frame_id', '=', $request->frame_id)
                ->where('tags.contact_id', '=', $request->contact_id)->first();

            if ($verify_tag) {
                return  response()->json(['error' => 'You are already tag this contact']);
            }

            $input = $request->only('frame_id', 'contact_id');
            // var_dump($input);
            $tag = Tag::create($input);

            $data = [
                'tag' => $tag,
                'message' => $user->firstname. ' '. $user->lastname .' Tags '. $contact->contact_firstname . ' ' . $contact->contact_lastname .' on a frame',
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
}
