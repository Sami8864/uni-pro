<?php

namespace App\Http\Controllers\api;


use App\Models\Device;
use App\Traits\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Models\Notification as NotificationModel;
use App\Models\Notification as ModelsNotification;

class NotificationController extends Controller
{
    use Notification;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function saveToken(Request $request)
    {
        $this->token($request->token);
        Log::info("token saved  " . $request->token ,[$this->token($request->token)]);
        return response()->json(['token saved successfully.']);
    }

    public function sendNotification(Request $request)
    {
        $token = Device::pluck('device_token')->all();
        $response = $this->send('hi', 'i am from titans', ['dwvWXC1sOU7qrK-ghidmWc:APA91bGk1pWZbbjB8DVPOL5sRshdXbHIVQuuBAFi15kzA1zWg9aCI5YXbTGc-VQ88sXwS1IKsNB1i6hJZVYXMKHFmcypfi1Lt1DAbC8wAM9SDSd4lUxny-PfKD9VE4FI9VvFK1VmM_lE'], 'hello');
        dd($response);
    }

    public function mark_all_as_read()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
    public function delete_all()
    {
        auth()->user()->notifications()->delete();

        return back();
    }
    public function go_to($id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();
        auth()->user()->notifications->where('id', $id)->markAsRead();
        $url = json_decode($notification->data);
        return redirect($url->link);
    }
    public function list()
    {
        $unreadNotifications = auth()->user()->unreadNotifications()->paginate(10); // Fetch unread notifications

        return response()->json(['data' => $unreadNotifications, 'code' => 200]);
    }
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
    $notification->markAsRead(); // Laravel provides a convenient method for marking notifications as read
    return response()->json(['message' => 'Notification marked as read']);
    }


}
