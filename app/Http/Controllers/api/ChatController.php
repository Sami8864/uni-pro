<?php

namespace App\Http\Controllers\api;

use Pusher\Pusher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    
    public function getChannelId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
            'sender_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => []
            ], 400);
        }
        $rs = [$request->sender_id, $request->receiver_id];
        $chat = Chat::whereIn('sender_id', $rs)
            ->whereIn('receiver_id', $rs)
            ->with(array('messages' => function ($query) {
                $query->where('deleted_user_id', '!=', 0)
                    ->where('deleted_user_id', '!=', auth('api')->user()->id)
                    ->orWhereNull('deleted_user_id')
                    ->with('files');
            }))

            ->get()
            ->first();
        if ($chat != []) {
            return response()->json([
                'statusCode' => 200,
                'message' => 'Channel ID against receiver_id: ' . $request->receiver_id . ' sender_id: ' . $request->sender_id,
                'successData' => ['chat' => $chat]
            ], 200);
        } else {
            if ($request->receiver_id != $request->sender_id) {
                $chat = new Chat;
                $chat->channel_id = Str::random(30) . '-' . $request->receiver_id . '-' . $request->sender_id;
                $chat->receiver_id = (string) $request->receiver_id;
                $chat->sender_id = (string) $request->sender_id;
                $chat->save();
                $chat->messages = [];
                return response()->json([
                    'statusCode' => 200,
                    'message' => 'Channel ID against receiver_id: ' . $request->receiver_id . ' sender_id: ' . $request->sender_id,
                    'successData' => ['chat' => $chat]
                ], 200);
            } else {
                return response()->json([
                    'statusCode' => 400,
                    'message' => 'Sender and Receiver ID Conflict',
                    'successData' => []
                ], 200);
            }
        }
    }



    public function sendMessage(Request $request)
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true
        ];
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = [
            'message' => $request->input('message')
        ];

        $pusher->trigger('chat-channel', 'chat-event', $data);

        return response()->json(['status' => 'Message Sent!']);
    }
}
