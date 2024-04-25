<?php

namespace App\Http\Controllers\api;

use App\Models\Headshots;
use App\Models\SavedFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSavedFeedRequest;
use App\Http\Requests\UpdateSavedFeedRequest;

class SavedFeedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function deletFeed(Request $request)
    {
        $user = auth()->user();
        if (isset($user)) {
            $data =   SavedFeed::where('user_id', $user->id)->where('profile_id', $request->id)->delete();
            $responseData = [
                'success' =>  $data,
                'data' => [
                    'message' => "Host deleted with success"
                ]
            ];
            return response()->json($responseData);
        } else {
            $responseData = [
                'success' =>  false,
                "message" => "User  not found",
                "errorCode" => 6002,
                "status" => 404
            ];
            return response()->json($responseData);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function savePost(Request $request)
    {
        $user = auth()->user();
       
        $postId = $request->device_id;
      
        $existingSavedPost = SavedFeed::where('user_id', $user->id)->where('profile_id', $postId)->first();
      
        // If the post is already saved, return a response indicating it's already saved
        if ($existingSavedPost) {
            
            return response()->json(['message' => 'Profile is already saved', "success" => false , ]);
        }
        Log::info("User id for save feed" ,[$user->id]);
        Log::info("profile_id  for save feed" ,[$postId]);
        // If the post is not already saved, save it
        $savedPost = new SavedFeed();
        $savedPost->user_id = $user->id;
        $savedPost->profile_id = $postId;
        $savedPost->save();
    
        return response()->json(['message' => 'Profile saved successfully', "success" => true]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getSavedPosts()
    {

        $user = auth()->user();
        $savedPosts = SavedFeed::where('user_id', $user->id)->with('feed')->get();
        $arr = [];
        foreach ($savedPosts as $dev) {
            $arr[] = Headshots::where('device_id', $dev->profile_id)->where('type_id', 2)->first();
        }
        return response()->json([
            'code' => 200, 'message' => 'Data Fetched',
            'data' => $arr
        ], 200);
        return response()->json($savedPosts);
    }

    /**
     * Display the specified resource.
     */
    public function show(SavedFeed $savedFeed)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SavedFeed $savedFeed)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSavedFeedRequest $request, SavedFeed $savedFeed)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SavedFeed $savedFeed)
    {
        //
    }
}
