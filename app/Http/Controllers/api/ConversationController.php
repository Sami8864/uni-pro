<?php

namespace App\Http\Controllers\api;

use Pusher\Pusher;
use App\Models\User;
use App\Models\Device;

use App\Models\Message;
use App\Models\FilmMaker;
use App\Models\Headshots;
use App\Traits\FileUpload;
use App\Events\MessageSent;
use Illuminate\Support\Str;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\ChatMessageFile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessagesResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ConversationResource;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Models\Notification as NotificationModel; // Alias the Notification model
use App\Traits\Notification as NotificationTrait; // Alias the Notification trait

class ConversationController extends Controller
{
    use NotificationTrait;
    public function getChannelId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => []
            ], 400);
        }
        $sender_id = request()->user()->id;
        $rs = [$sender_id, $request->receiver_id];
        $chat = Conversation::whereIn('sender_id', $rs)
            ->whereIn('receiver_id', $rs)
            ->get()
            ->first();
        if ($chat != []) {
            return response()->json([
                'statusCode' => 200,
                'message' => 'Channel ID against receiver_id: ' . $request->receiver_id . ' sender_id: ' .  $sender_id,
                'successData' => ['chat' => $chat]
            ], 200);
        } else {
            if ($request->receiver_id != $sender_id) {
                $chat = new Conversation;
                $chat->channel_id = Str::random(30) . '-' . $request->receiver_id . '-' . $sender_id;
                $chat->receiver_id = (string) $request->receiver_id;
                $chat->sender_id = (string) $sender_id;
                $chat->save();
                $chat->messages = [];
                return response()->json([
                    'statusCode' => 200,
                    'message' => 'Channel ID against receiver_id: ' . $request->receiver_id . ' sender_id: ' . $sender_id,
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
    public function fetchMessages()
    {
        return Message::get();
    }
    public function index()
    {

        $conversations = request()->user()->conversations;
        
        $users = User::query()
            ->where(function ($query) {
                $query->whereHas('started_chat_with', function ($query) {
                    $query
                        ->where('receiver_id', request()->user()->id)
                        ->where('sender_id', '!=', request()->user()->id);
                })->orwhereHas('responded_to_chat', function ($query) {
                    $query->where('receiver_id', '!=', request()->user()->id)
                        ->where('sender_id', request()->user()->id);
                });
            })
            ->where('id', '!=', request()->user()->id)
            ->select(['id', 'name'])
            ->get();

        $users->map(function ($user) {

            $user->lastest_message = Message::query()
                ->whereHas('conversation', function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', request()->user()->id);
                })
                ->orwhereHas('conversation', function ($query) use ($user) {
                    $query->where('receiver_id', $user->id)
                        ->where('sender_id', request()->user()->id);
                })
                ->select(['message','created_at'])
                ->latest()
                ->first();
                return response()->json(['data'=>$user->lastest_message]);
            $user->unread_count = Message::query()
                ->where(function ($subquery) use ($user) {
                    $subquery->whereHas('conversation', function ($query) use ($user) {
                        $query->where('sender_id', $user->id)
                            ->where('receiver_id', request()->user()->id);
                    })
                        ->orwhereHas('conversation', function ($query) use ($user) {
                            $query->where('receiver_id', $user->id)
                                ->where('sender_id', request()->user()->id);
                        });
                })
                ->where('sender_id', $user->id)
                ->where('read_at', null)
                ->count();

            return $user;
        });

        return response()->json(['code' => 200, 'data' =>  ConversationResource::collection($users), 'message' => 'Conversation has been Fetched succcessfuly']);
    }
    public function delete_conversation(Request $request, $id)
    {

        $user = User::select(['id', 'name'])->find($id);
        $conversation_check = Conversation::where([['sender_id', request()->user()->id], ['receiver_id', $user->id]])
            ->orWhere([['sender_id', $user->id], ['receiver_id', request()->user()->id]])->get();

        return response()->json(['message' => 'conversation deleted successfully' , 'code' => 200,]);
    }
    public function sendMessageByChannelId(Request $request, $id)
    {
        $data = $request->All();
        
        $validator = Validator::make($data, [
            'message' => 'required',
            'channel_id' => 'required|exists:conversations,channel_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first(), 'code' => 422], 422);
        } else {
            $user = Auth::user();

            $conversation_check = Conversation::where('channel_id', $request->channel_id)->first();

            if (!$conversation_check) {
                Conversation::create([
                    'sender_id' => auth()->id(),
                    'receiver_id' => $id
                ]);
            }
            $conversation = Conversation::query()
                ->where('channel_id', $request->channel_id)
                ->first();
            $muted=Conversation::where('sender_id', auth()->id())->where('receiver_id', $id)->value('receiver_muted');
           
            if ($request->hasFile('file')) {
                $filename = FileUpload::imageUpload($request->file('file'), 'message/');
                $f = new ChatMessageFile;
                $f->chat_message_id = (string)  $conversation_check->id;
                $f->file_path = $filename;
                $f->save();
                $conversation->messages()->create([
                    'message' =>  $filename,
                    'sender_id' => request()->user()->id
                ]);
                event(new MessageSent($request->channel_id, json_decode($filename, true)));
                $pusher = new Pusher("aab619730d10cbbda792", "21e833056b1c795f1d90", "1757459", array('cluster' => 'ap4'));
                $pusher->trigger($request->channel_id, 'users.chat', json_decode($filename, true));
            } else {
                $conversation->messages()->create([
                    'message' => $request->message,
                    'sender_id' => request()->user()->id
                ]);
            }
            $message = $conversation->lastest_message();
            $message->receiver_id = request()->user()->id == $conversation->sender_id ? $conversation->receiver_id : $conversation->sender_id;
            Log::info('Recevier Id', [$message->receiver_id]);
            event(new MessageSent($request->channel_id,$muted, json_decode($message, true)));
            $headshot = FilmMaker::where('user_id', request()->user()->id)->select(['compnay_name','profile_image', 'full_name'])->first();
            Log::info('profile image', [ $headshot]);
            $arry['message'] = json_decode($message, true);
            $arry['channel_id'] = $request->channel_id;
            $arry['details'] =  $headshot;
            $arry['muted'] =$muted;
            $pusher = new Pusher("aab619730d10cbbda792", "21e833056b1c795f1d90", "1757459", array('cluster' => 'ap4'));
            $message['muted'] =$muted;
            $pusher->trigger($request->channel_id, 'users.chat', json_decode($message, true));
            $notification = NotificationModel::create([
                'id' => (string) Str::uuid(), // Generate UUID and cast to string
                'type' => 'message', // Adjust as needed
                'notifiable_type' => User::class, // Assuming you're associating the notification with the User model
                'notifiable_id' => $id, // Provide the actual id of the user
                'data' => $arry // Adjust as needed
            ]);
            Log::info('array' , [ $notification ]);
            $id = User::where('id', request()->user()->id)->first();
            Log::info('user that get the message', [$id]);
           if( $muted === true)
           { 
            return response()->json(['code' => 200, 'data' => new MessagesResource($message), 'message' => 'message has been sent succcessfuly']);
           }
           $token = Device::where('user_id',$message->receiver_id)->latest()->pluck('device_token')->first();
           $response = $this->send($message->sender->name, $message->message, [$token], $id->getdata(), $muted, 'message');
           Log::info('User fcm Token', [$token]);
           Log::info('response of fcm ', [$response]);
           
           return response()->json(['code' => 200, 'data' => new MessagesResource($message), 'message' => 'message has been sent succcessfuly']);
        }
    }

    public function messages($id)
    {
     
        $user = User::select(['id', 'name'])->find($id);

        $conversation_check = Conversation::where([['sender_id', request()->user()->id], ['receiver_id', $user->id]])
            ->orWhere([['sender_id', $user->id], ['receiver_id', request()->user()->id]])->first();

        if (!$conversation_check) {
            Conversation::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $user->id
            ]);
        }
        Message::query()
            ->whereHas('conversation', function ($query) use ($id) {
                $query->where('sender_id', $id)
                    ->where('receiver_id', request()->user()->id);
            })
            ->orwhereHas('conversation', function ($query) use ($id) {
                $query->where('receiver_id', $id)
                    ->where('sender_id', request()->user()->id);
            })->where('sender_id', $id)
            ->update([
                'read_at' => now()
            ]);

        $messages = Conversation::query()
            ->with('messages', function ($query) {
                $query->with('sender')->where([['sender_id', request()->user()->id], ['sender_deleted_at', null]])
                    ->orWhere([['sender_id', '!=', request()->user()->id], ['receiver_deleted_at', null]])->latest()->get();;
            })
            ->where([['sender_id', request()->user()->id], ['receiver_id', $id]])
            ->orWhere([['sender_id', $id], ['receiver_id', request()->user()->id]])
            ->first();

        $conversation_blocked_check = Conversation::query()
            ->where([['sender_id', request()->user()->id], ['receiver_id', $user->id]])
            ->orWhere([['sender_id', $user->id], ['receiver_id', request()->user()->id]])
            ->first();

        $user->blocked_by = $conversation_blocked_check->blocked ? $conversation_blocked_check->blocked : null;

        return response()->json(['user' => $user, 'messages' => MessagesResource::collection($messages->messages), 'current_user' => request()->user(), 'code' => 200]);
    }

    public function delete_message(Message $message)
    {

        if ($message->sender_id  == request()->user()->id) {
            $message->update([
                'sender_deleted_at' => now(),
            ]);
        } else {
            $message->update([
                'receiver_deleted_at' => now(),
            ]);
        }
        return response()->json(['message' => 'Message deleted', 'id' => $message->id, 'code' => 200 ] );
    }
    public function delete_chat($id)
    {
        // $message = Message::query()
        // ->whereHas('conversation', function($query) use ($id){
        //     $query->where('first_user', $id)
        //     ->where('second_user', request()->user()->id);
        // })
        // ->orwhereHas('conversation', function($query) use ($id){
        //     $query->where('second_user', $id)
        //     ->where('first_user', request()->user()->id);
        // });
        $sent = Message::query()
            ->whereHas('conversation', function ($query) use ($id) {
                $query->where('sender_id', $id)
                    ->where('receiver_id', request()->user()->id);
            })
            ->orwhereHas('conversation', function ($query) use ($id) {
                $query->where('receiver_id', $id)
                    ->where('sender_id', request()->user()->id);
            })
            ->where('sender_id', '!=', request()->user()->id);

        $received = Message::query()
            ->whereHas('conversation', function ($query) use ($id) {
                $query->where('sender_id', $id)
                    ->where('receiver_id', request()->user()->id);
            })
            ->orwhereHas('conversation', function ($query) use ($id) {
                $query->where('receiver_id', $id)
                    ->where('sender_id', request()->user()->id);
            })
            ->where('sender_id', request()->user()->id);

        $sent->update([
            'receiver_deleted_at' => now()
        ]);

        $received->update([
            'sender_deleted_at' => now()
        ]);

        return response()->json(['message' => 'chat deleted successfully', 'code' => 200]);
    }
    public function block_chat($conversation)
    {
        Conversation::query()
            ->where([['sender_id', request()->user()->id], ['receiver_id', $conversation]])
            ->orWhere([['sender_id', $conversation], ['receiver_id', request()->user()->id]])
            ->update([
                'blocked' => request()->user()->id
            ]);

        return response()->json(['id' => request()->user()->id]);
    }
    public function unblock_chat($conversation)
    {
        Conversation::query()
            ->where([['sender_id', request()->user()->id], ['receiver_id', $conversation]])
            ->orWhere([['sender_id', $conversation], ['receiver_id', request()->user()->id]])
            ->update([
                'blocked' => 0
            ]);

        return response()->json(['id' => 0]);
    }
    public function prev_messages($id, $last_message_id)
    {
        $messages = Conversation::query()
            ->with('messages', function ($query) use ($last_message_id, $id) {
                $query->with('sender')
                    ->where(function ($query) use ($id) {
                        $query->where([['sender_id', request()->user()->id], ['sender_deleted_at', null]])
                            ->orWhere([['sender_id', '!=', request()->user()->id], ['receiver_deleted_at', null]]);
                    })
                    ->where('id', '<', $last_message_id)
                    ->latest()->limit(10)->get();;
            })
            ->where([['sender_id', request()->user()->id], ['receiver_id', $id]])
            ->orWhere([['sender_id', $id], ['receiver_id', request()->user()->id]])
            ->first();
        return response()->json(['messages' => MessagesResource::collection($messages->messages)]);
    }

    public function allUsers()
    {
        try {
            $data =  User::wherenot('id', request()->user()->id)->get();
            if ($data) {
                return response()->json(['code' => 200, 'data' => $data]);
            }

            return response()->json(['code' => 200, 'message' => 'no users exist']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    // Mute conversation
    public function mute(Request $request, $userId)
    {
        // Find conversations associated with the user ID
        $conversations = Conversation::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get();

        foreach ($conversations as $conversation) {
            // Determine if the current user is the sender or receiver
            if ($request->user()->id == $conversation->sender_id) {
                $conversation->sender_muted = true;
            } elseif ($request->user()->id == $conversation->receiver_id) {
                $conversation->receiver_muted = true;
            }
            $conversation->save();
        }

        return response()->json(['message' => 'Conversations muted successfully' , 'code' => 200]);
    }

    // Unmute conversation
    public function unmute(Request $request, $userId)
    {
        // Find conversations associated with the user ID
        $conversations = Conversation::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get();

        foreach ($conversations as $conversation) {
            // Determine if the current user is the sender or receiver
            if ($request->user()->id == $conversation->sender_id) {
                $conversation->sender_muted = false;
            } elseif ($request->user()->id == $conversation->receiver_id) {
                $conversation->receiver_muted = false;
            }
            $conversation->save();
        }

        return response()->json(['message' => 'Conversations unmuted successfully', 'code' => 200]);
    }
}
