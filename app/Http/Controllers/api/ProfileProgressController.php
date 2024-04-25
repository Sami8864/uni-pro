<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\ProfileProgress;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ProfileProgressController extends Controller
{
    public function getBatteryLevel(Request $request){

    $data=$request->all();
    $validator=Validator::make($data,[
        'device_id'=>'required|exists:profile_progress,id'
    ]);
    if($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }
    else{
        return ProfileProgress::where('id',$data['device_id'])->pluck('battery_level')->first();
      }
    }
}
