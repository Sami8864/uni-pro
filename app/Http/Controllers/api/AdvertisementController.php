<?php

//namespace App\Http\Controllers;
namespace App\Http\Controllers\api;


use App\Traits\FileUpload;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{
    use FileUpload;

    public function show(Request $request)
    {
        return response()->json([
            'message' => 'advertisement fetched',
            'advertisement' => Advertisement::where('status', 1)->first()
        ], 200);
    }
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'name' => 'required',
            'video_url' => 'required', // Assuming video_url should be a valid URL
            'status' => 'required',
        ]);

        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $filename = FileUpload::file($request->file('video_url'), 'advertisements/');
        // Validation passed, so add the data to the database
        $newRecord = Advertisement::create([
            'description' => $request->input('description'),
            'name' => $request->input('name'),
            'status' => $request->input('status'),
            'video_url' => $filename,
        ]);

        return response()->json([
            'message' => 'Advertisement added successfully',
            'code' => 200,
            'data' => $newRecord,
        ], 200);
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'id' => 'required', // Assuming video_url should be a valid URL
        ]);
        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $advertisement = Advertisement::find($data['id']);
        if (!$advertisement) {
            return response()->json(['error' => 'Advertisement not found', 'code'=> 404]);
        }
        $url =  $advertisement->video_url;
        // Delete the video file if it exists
        if (Storage::disk('public')->exists($url)) {
            Storage::disk('public')->delete($url);
            $advertisement->delete();
            return response()->json(['message' => 'Advertisement deleted successfully'], 200);
        }
        return response()->json(['errror' => 'not exist path'], 422);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'name' => 'required',
            'video_url' => 'required', // Assuming video_url should be a valid URL
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $advertisement = Advertisement::find($request->id);
        if (!$advertisement) {
            return response()->json(['error' => 'Advertisement not found'], 404);
        }

        $url =  $advertisement->video_url;
        // Delete the video file if it exists
        if (Storage::disk('public')->exists($url)) {
            Storage::disk('public')->delete($url);
        }
        else{
            return response()->json(['errror' => 'not exist path'], 422);
        }
        // Delete the old associated video file
        //   $this->delete($advertisement->video_url, 'public/advertisements');
        $filename = FileUpload::file($request->file('video_url'), '/advertisements');
        // Update the record in the database
        $advertisement->update([
            'description' => $request->input('description'),
            'name' => $request->input('name'),
            'video_url' => $filename
        ]);
        return response()->json(['message' => 'Advertisement updated successfully', 'data' => $advertisement], 200);
    }
}
