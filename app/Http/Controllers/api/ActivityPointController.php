<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Device;
use App\Models\Transaction;
use App\Models\UserSpending;
use Illuminate\Http\Request;
use App\Models\ActivityPoint;
use App\Models\ProfileProgress;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreActivityPointRequest;
use App\Http\Requests\UpdateActivityPointRequest;
use App\Traits\Notification as NotificationTrait; // Alias the Notification trait
class ActivityPointController extends Controller
{
    use NotificationTrait;
    /**
     * Display a listing of the resource.
     */
    public function load()
    {
        try {
            $data = ActivityPoint::first();
            return response()->json(['data' => $data, 'code' => 200]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage(), 'code' => 422]);
        }
    }
    public function index()
    {
        try {
            $data = ActivityPoint::first();
            $devices = User::where('id', auth()->user()->id)->value('profileprogess_id');
            $device = ProfileProgress::where('id', $devices)->first();
            $data['current_battery'] =  $device->types_points;
            $data['available_contacts'] =  $device->available_contacts;
            if(!isset($data['available_contacts'])){
                $data['available_contacts']='0';
            }
            return response()->json(['data' => $data, 'code' => 200]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage(), 'code' => 422]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function purchase(Request $request)
    {

        $data = $request->All();
        Log::info('Request of Data', [$data]);
        $validator = Validator::make($data, [
            'activity_points_id' => 'required|exists:activity_points,id',
            'interval_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {
            $user = User::find(auth()->user()->id);
            $device = ProfileProgress::where('id', $user->profileprogess_id)->first();
            $intervalNum = json_decode($data['activity_points_id']);
            $intervalNumber = json_decode($data['interval_number']);
            $interval = ActivityPoint::where('id', $intervalNum)->first();
            Log::info('Interval', [$interval]);
            Log::info('Interval size ', [$interval->intervalsize]);
            $device->types_points =  $device->types_points + ($interval->intervalsize * $intervalNumber);

            $device->available_contacts = $device->available_contacts + ($intervalNumber * $interval->perintervalcontact);

            if ($device->types_points < 50) {
                $device->account_level = 1;
            } else if ($device->types_points > 50  && $device->types_points < 120) {
                $device->account_level = 2;
            } else  if ($device->types_points > 300) {
                $device->account_level = 3;
            }
            $device->save();
            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'transaction_amount' => $interval->perintervalprice * $intervalNumber,
            ]);
            $id = User::where('id',auth()->user()->id)->first();
            $token = Device::where('user_id',auth()->user()->id)->latest()->pluck('device_token')->first();
            $response = $this->send('Cast Types' , 'You Have Eraned  '. $intervalNumber, [$token] , $id->getdata(), 'True' , 'spend');
            UserSpending::create([
                'user' => auth()->user()->id,
                'amount' => $intervalNumber,
                'spending_type' => 'Points',
                'transaction_type' => 'Earned'
            ]);
            Log::info('response', [$transaction]);
            return response()->json([
                'code' => 200,
                'message' => 'Transaction Successfull',
                'transaction' => $transaction
            ], 200);
        }
    }


    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'upperlimit' => 'required|integer',
                'lowerlimit' => 'required|integer',
                'intervalsize' => 'required|integer',
                'perintervalcontact' => 'required|integer',
                'perintervalprice' => 'required|integer',
            ]);

            // Create a new activity point instance
            $activityPoint = new ActivityPoint();
            $activityPoint->upperlimit = $validatedData['upperlimit'];
            $activityPoint->lowerlimit = $validatedData['lowerlimit'];
            $activityPoint->intervalsize = $validatedData['intervalsize'];
            $activityPoint->perintervalcontact = $validatedData['perintervalcontact'];
            $activityPoint->perintervalprice = $validatedData['perintervalprice'];

            // Save the activity point
            $activityPoint->save();

            // Optionally, you can return a response or redirect somewhere
            return response()->json(['message' => 'Activity point created successfully'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage(), 'code' => 422]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityPoint $activityPoint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActivityPoint $activityPoint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'upperlimit' => 'required|integer',
                'lowerlimit' => 'required|integer',
                'intervalsize' => 'required|integer',
                'perintervalcontact' => 'required|integer',
                'perintervalprice' => 'required|integer',
            ]);
            $data =  ActivityPoint::where('id', $request->id)->update([
                'upperlimit' => $request->upperlimit,
                'lowerlimit' => $request->lowerlimit,
                'intervalsize' => $request->intervalsize,
                'perintervalcontact' => $request->perintervalcontact,
                'perintervalprice' => $request->perintervalprice,
            ]);
            return response()->json(['message' => 'Activity point Updated successfully', 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage(), 'code' => 422]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityPoint $activityPoint)
    {
        //
    }
}
