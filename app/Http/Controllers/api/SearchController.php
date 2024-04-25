<?php


namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Headshots;
use App\Models\SavedFeed;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use App\Models\UserAttribute;
use App\Models\ProfileProgress;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {

        // Get the search parameters from the request
        $params = $request->data;
        // dd( $params);
        // Query the database based on the search parameters
        $query = UserDetail::query();

        if (isset($params['age'])) {
            $query->whereBetween('age', [$params['age']['min'], $params['age']['max']]);
        }
      
        // Check if ethnicity parameter is provided
        if (isset($params['ethnicity']) && !empty($params['ethnicity'])) {
            $query->whereIn('ethnicity', $params['ethnicity']);
        }
        
        // if (isset($params['gender'])) {
        //     $query->where('gender', $params['gender']);
        // }
        
        if (isset($params['heightInMeters'])) {
            $query->where('height', '<=', $params['heightInMeters']);  // Assuming 'height' column in meters
        }

        if (isset($params['weightInPounds'])) {
            $query->where('weight', '<=', $params['weightInPounds']); // Assuming 'weight' column in pounds
        }

        // Execute the query and return the results
        $results = $query->get();
        $arr = [];
        foreach ($results as $dev) {
            $arr[] = Headshots::where('device_id', $dev->device_id)->where('type_id', 2)->first();
        }
        return response()->json([
            'code' => 200, 'message' => 'Data Fetched',
            'data' => $arr
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function searchByName(Request $request)
    {
        $params = $request->name;
    
        $query = UserDetail::query();

        if (isset($params)) {
            $query->where('name', 'like', '%' . $params . '%');
        }
        $results = $query->get();
        
        $arr = [];
        foreach ($results as $dev) {
            $arr[] = Headshots::where('device_id', $dev->device_id)->where('type_id', 2)->value('device_id');
        }
       
        $user = auth()->user();
        $savedProfileIds = SavedFeed::where('user_id', $user->id)->pluck('profile_id')->toArray();
   
        $profiles = ProfileProgress::whereIn('id', $arr)->get();
        
        // Fetch additional data for each profile if needed
        $arr1 = [];
        foreach ($profiles as $profile) {
            $profileData = $profile->toArray();

            // Check if profile is saved
            $profileData['is_saved'] = in_array($profile->id, $savedProfileIds);

            // Fetch headshot for the profile
            $headshot = Headshots::where('device_id', $profile->id)->where('type_id', 2)->first();

            if ($headshot !== null) {
                // Add headshot data to profile data
                $profileData['headshot'] = $headshot;
            }

            $arr1[] = $profileData;
        }
        
        return response()->json([
            'code' => 200, 'message' => 'Data Fetched',
            'data' => $arr1
        ], 200);
    }
    public function searchBySavedName(Request $request)
    {
        $params = $request->name;
    
        $query = UserDetail::query();

        if (isset($params)) {
            $query->where('name', 'like', '%' . $params . '%');
        }
        $results = $query->get();
        
        $arr = [];
        foreach ($results as $dev) {
            $arr[] = Headshots::where('device_id', $dev->device_id)->where('type_id', 2)->value('device_id');
        }
       
        $user = auth()->user();
        $savedProfileIds = SavedFeed::where('user_id', $user->id)->pluck('profile_id')->toArray();
   
        $profiles = ProfileProgress::whereIn('id', $arr)->get();
        
        // Fetch additional data for each profile if needed
        $arr1 = [];
        foreach ($profiles as $profile) {
            $profileData = $profile->toArray();

            // Check if profile is saved
            $profileData['is_saved'] = in_array($profile->id, $savedProfileIds);

            // Fetch headshot for the profile
            $headshot = Headshots::where('device_id', $profile->id)->where('type_id', 2)->first();

            if ($headshot !== null) {
                // Add headshot data to profile data
                $profileData['headshot'] = $headshot;
            }

            $arr1[] = $profileData;
        }
        
        return response()->json([
            'code' => 200, 'message' => 'Data Fetched',
            'data' => $arr1
        ], 200);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
