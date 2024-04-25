<?php

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
    public function sendMessageByChannelId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => 'required',
            'sender_id' => 'required',
            'receiver_id' => 'required',
            //'message' => 'required',
            //'files' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => []
            ], 400);
        }
        $chat = Chat::where('channel_id', $request->channel_id)
            ->get()
            ->first();
        $m = new ChatMessage;
        $m->chat_id =  (string) $chat->id;
        $m->sender_id = (string) $request->sender_id;
        $m->receiver_id = (string) $request->receiver_id;
        $m->message = (string) $request->message;
        $m->service_id = (string) $request->service_id;
        $data = array();
        if ($request->hasFile('files')) {
            if ($request->has('msgType')) {
                $m->message_type = $request->msgType;
            } else {
                $m->message_type = 'multipart';
            }
            $m->save();
            foreach ($request->file('files') as $file) {
                $fileName = time() . rand(10, 100) . '.' . $file->extension();
                $file->move("public/upload/chat_message_files", $fileName);
                $f = new ChatMessageFile;
                $f->chat_message_id = (string) $m->id;
                $f->file_path = "/public/upload/chat_message_files/" . $fileName;
                $f->file_type = $file->getClientOriginalExtension();
                $f->save();
                array_push($data, $f);
            }
        } else {
            $m->message_type = 'text';
            $m->save();
        }
        $m->files = $data;
        $chat->messages = $m;
        event(new RealTimeMessage($request->channel_id, json_decode($chat, true)));
        $pusher = new Pusher("8ce156c401676ab0da3a", "83b5ec131681ef39e6ba", "1366915", array('cluster' => 'mt1'));
        $pusher->trigger($request->channel_id, 'users.chat', json_decode($chat, true));
        if (Chat::where('sender_id', '0')->where('receiver_id', $request->receiver_id)->exists()) {
            $channel = Chat::where('sender_id', '0')
                ->where('receiver_id', $request->receiver_id)
                ->get()
                ->first();

            $user = User::where('id', $request->sender_id)
                ->with('agent')
                ->get()
                ->first();

            $message_id = ChatMessage::where('sender_id', $request->sender_id)
                ->where('receiver_id', $request->receiver_id)
                ->selectRaw('max(id) as id')
                ->get();


            $message = ChatMessage::where('id', $message_id[0]->id)
                ->get()
                ->first();

            $countt = ChatMessage::where('read_status', '0')
                ->where('sender_id', $request->sender_id)
                ->where('receiver_id', $request->receiver_id)
                ->get()
                ->count();


            $user['last_message'] = $message->message;
            $user['count'] = $countt;

            $pusher->trigger($channel->channel_id, 'users.chat', ['myChats' => $user]);
        }
        if (Chat::where('sender_id', '0')->where('receiver_id', $request->sender_id)->exists()) {
            $channel = Chat::where('sender_id', '0')
                ->where('receiver_id', $request->sender_id)
                ->get()
                ->first();

            $user = User::where('id', $request->receiver_id)
                ->with('agent')
                ->get()
                ->first();

            $message_id = ChatMessage::where('sender_id', $request->receiver_id)
                ->where('receiver_id', $request->receiver_id)
                ->selectRaw('max(id) as id')
                ->get();


            $message = ChatMessage::where('id', $message_id[0]->id)
                ->get()
                ->first();

            $countt = ChatMessage::where('read_status', '0')
                ->where('sender_id', $request->receiver_id)
                ->where('receiver_id', $request->sender_id)
                ->get()
                ->count();

            //            $user['last_message'] = $message->message;
            //            $user['count'] = $countt;

            $pusher->trigger($channel->channel_id, 'users.chat', ['myChats' => $user]);
        }
        if ($request->receiver_id == '1' or $request->sender_id == '1' or $request->receiver_id == '1032' or $request->sender_id == '1032') {
            $adminChannel = Chat::where('sender_id', '0')
                ->where('receiver_id', '1')
                ->get()
                ->first();
            event(new RealTimeMessage($adminChannel->channel_id, json_decode($chat, true)));
            $pusher->trigger($adminChannel->channel_id, 'users.chat', json_decode($chat, true));
        }
        if ($request->receiver_id != '1') {
            $user = User::where('id', $request->sender_id)
                ->get()
                ->first();
            notification('RazzaQ Chat Alert', $user->name . " sents you the new message. Can you want to Reply↩️", $request->receiver_id, 0, 'Chat', $request->channel_id);
        }
        return response()->json([
            'statusCode' => 200,
            'message' => 'Message sent successfully',
            'successData' => [
                'receiveChat' => $chat
            ]
        ], 200);
    }
    public function getAllMessagesByChannelId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => 'empty'
            ], 400);
        }
        $chat = Chat::where('channel_id', $request->channel_id)
            ->with('messages')
            ->with('messages.files')
            ->get()
            ->first();
        return response()->json([
            'statusCode' => 200,
            'message' => 'Messages fetched successfully',
            'successData' => [
                'chat' => $chat
            ]
        ], 200);
    }
    public function getAllMessagesBySRId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => 'empty'
            ], 400);
        }
        $sender_id = $request->sender_id;
        $receiver_id = $request->receiver_id;
        $chat = Chat::where('sender_id', $sender_id)
            ->where('receiver_id', $receiver_id)
            ->with('messages')
            ->with('messages.files')
            ->get()
            ->first();
        if ($chat == Null) {
            $sender_id = $request->receiver_id;
            $receiver_id = $request->sender_id;
        }
        $chat = Chat::where('sender_id', $sender_id)
            ->where('receiver_id', $receiver_id)
            ->with('messages')
            ->with('messages.files')
            ->get()
            ->first();
        return response()->json([
            'statusCode' => 200,
            'message' => 'Messages fetched successfully',
            'successData' => [
                'chat' => $chat
            ]
        ], 200);
    }
    public function deleteMessageById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => 'empty'
            ], 400);
        }
        $message = ChatMessage::where('id', $request->message_id);
        //$files = ChatMessageFile::where('chat_message_id',$request->message_id);
        if ($message != Null) {
            $message->delete();
        }
        //        if($files!=Null){
        //            $files->delete();
        //        }
    }
    public function deleteMessageFileById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_file_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => 'empty'
            ], 400);
        }
        $files = ChatMessageFile::where('chat_message_id', $request->message_id);
        if ($files != Null) {
            $files->delete();
        }
    }
    public function getNewMessageCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => []
            ], 400);
        }

        $totalCount = ChatMessage::where(array('read_status' => '0', 'receiver_id' => $request->user_id))
            ->count();

        $individualCount = ChatMessage::where(array('read_status' => '0', 'receiver_id' => $request->user_id))
            ->selectRaw('count(sender_id) as count, sender_id')
            ->groupBy('sender_id')
            ->get();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Messages count calculated successfully',
            'successData' => [
                'total_count' => $totalCount,
                'individual_Count' => $individualCount
            ]
        ], 200);
    }
    public function readAllMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 200,
                'message' => $validator->errors() - first(),
                'successData' => []
            ], 200);
        }
        $messages = ChatMessage::where(array('sender_id' => $request->sender_id, 'receiver_id' => $request->receiver_id, 'read_status' => '0'))
            ->update(['read_status' => '1']);
        return response()->json([
            'statusCode' => 200,
            'message' => 'All messages read successfully',
            'successData' => ['messages' => $messages]
        ], 200);
    }
    public function myChatUsers2(Request $request)
    {

        $packingMovingUserChatId = 1032;
        //$packingMovingUserChatId = 2;

        $chats = Chat::where('sender_id', '<>', 0)
            ->where('receiver_id', '<>', 0)
            ->get();

        $chat_user_ids = array();

        foreach ($chats as $chat) {
            if ($chat->sender_id == 1 || $chat->sender_id == $packingMovingUserChatId) {
                array_push($chat_user_ids, ['receiver_id' => $chat->receiver_id, 'sender_id' => $chat->sender_id, 'chat_id' => $chat->id]);
            } else {
                if ($chat->receiver_id == 1 || $chat->receiver_id == $packingMovingUserChatId) {
                    array_push($chat_user_ids, ['receiver_id' => $chat->sender_id, 'sender_id' => $chat->receiver_id, 'chat_id' => $chat->id]);
                }
            }
        }

        $users = array();

        foreach ($chat_user_ids as $user) {

            $userr = User::where('id', $user['receiver_id'])
                ->with('agent')
                ->get()
                ->first();

            $last_message_id = ChatMessage::where('chat_id', $user['chat_id'])
                ->selectRaw('max(id) as id')
                ->get()
                ->first();

            $message = ChatMessage::where('id', $last_message_id->id)
                ->selectRaw('message , created_at')
                ->get()
                ->first();

            $individualCount = ChatMessage::where('read_status', '0')
                ->where('sender_id', $user['receiver_id'])
                ->where('receiver_id', $user['sender_id'])
                ->selectRaw('count(sender_id) as count')
                ->get()
                ->first();

            $m = strval($message);

            $array = str_split($m);
            $msg = array();
            $tm = array();
            $qc = 0;
            $pushMsg = false;
            $pushTm = false;
            foreach ($array as $char) {
                if ($pushMsg) {
                    array_push($msg, $char);
                }
                if ($pushTm) {
                    array_push($tm, $char);
                }
                if ($char == '"') {
                    $qc += 1;
                }
                if ($qc == 3) {
                    $pushMsg = true;
                }
                if ($qc == 4) {
                    $pushMsg = false;
                }
                if ($qc == 7) {
                    $pushTm = true;
                }
                if ($qc == 8) {
                    $pushTm = false;
                }
            }
            array_pop($msg);
            array_pop($tm);
            $msgp = implode("", $msg);
            $tmp = implode("", $tm);

            if ($userr) {
                $userr->count = (string)$individualCount->count;
                $userr->last_message = $msgp;
                $userr->last_msg_time_ago = $tmp;
                $userr->year = substr($tmp, 0, 4);
                $userr->month = substr($tmp, 5, 2);
                $userr->date = substr($tmp, 8, 2);
                $userr->hour = substr($tmp, 11, 2);
                $userr->minute = substr($tmp, 14, 2);
                $userr->second = substr($tmp, 17, 2);
                $userr->time = substr($tmp, 0, 4) . '-' . substr($tmp, 5, 2) . '-' . substr($tmp, 8, 2) . ' ' . substr($tmp, 11, 2) . ':' . substr($tmp, 14, 2) . ':' . substr($tmp, 17, 2);
                $userr->sender_id = (int)$user['sender_id'];
                array_push($users, $userr);
            }
        }

        usort($users, function ($firstItem, $secondItem) {
            $timeStamp1 = strtotime($firstItem->time);
            $timeStamp2 = strtotime($secondItem->time);
            return $timeStamp2 - $timeStamp1;
        });

        return response()->json([
            'statusCode' => 200,
            'message' => 'Chat Agents Fetched Successfully',
            'successData' => [
                'myChats' => $users
            ]
        ], 200);
    }
    public function myChatUsers(Request $request)
    {

        $chats = Chat::all();
        $chat_user_ids = array();
        foreach ($chats as $chat) {
            if ($chat->sender_id == $request->user_id) {
                if ($chat->receiver_id != '0') {
                    if (ChatMessage::where('sender_id', $chat->receiver_id)->where('receiver_id', $request->user_id)->exists() or ChatMessage::where('receiver_id', $chat->receiver_id)->where('sender_id', $request->user_id)->exists()) {
                        array_push($chat_user_ids, [$chat->receiver_id, $chat->id]);
                    }
                }
            } else if ($chat->receiver_id == $request->user_id) {
                if (ChatMessage::where('sender_id', $chat->sender_id)->where('receiver_id', $request->user_id)->exists() or ChatMessage::where('receiver_id', $chat->sender_id)->where('sender_id', $request->user_id)->exists()) {
                    if ($chat->sender_id != '0') {
                        array_push($chat_user_ids, [$chat->sender_id, $chat->id]);
                    }
                }
            }
        }

        $users = array();
        foreach ($chat_user_ids as $user) {

            $userr = User::where('id', $user[0])
                ->with('agent')
                ->get()
                ->first();

            $last_message_id = ChatMessage::where('chat_id', $user[1])
                ->selectRaw('max(id) as id')
                ->get()
                ->first();

            $message = ChatMessage::where('id', $last_message_id->id)
                ->selectRaw('message , created_at')
                ->get()
                ->first();

            $chat_id = ChatMessage::where('id', $last_message_id->id)
                ->selectRaw('chat_id ,deleted_user_id , created_at')
                ->get()
                ->first();

            $individualCount = ChatMessage::where('read_status', '0')
                ->where('sender_id', $user[0])
                ->where('receiver_id', $request->user_id)
                ->selectRaw('count(sender_id) as count')
                ->get()
                ->first();

            $m = strval($message);

            $array = str_split($m);
            $msg = array();
            $tm = array();
            $qc = 0;
            $pushMsg = false;
            $pushTm = false;
            foreach ($array as $char) {
                if ($pushMsg) {
                    array_push($msg, $char);
                }
                if ($pushTm) {
                    array_push($tm, $char);
                }
                if ($char == '"') {
                    $qc += 1;
                }
                if ($qc == 3) {
                    $pushMsg = true;
                }
                if ($qc == 4) {
                    $pushMsg = false;
                }
                if ($qc == 7) {
                    $pushTm = true;
                }
                if ($qc == 8) {
                    $pushTm = false;
                }
            }
            array_pop($msg);
            array_pop($tm);
            $msgp = implode("", $msg);
            $tmp = implode("", $tm);

            $chatID = $chat_id->chat_id;
            $chatEmpty = 0;
            $msg = (string)$chat_id->deleted_user_id;
            if ($msg === 'NULL') {
                $chatEmpty = 0;
            } else if ($msg === (string)$request->user_id) {
                $chatEmpty = 1;
            } else if ($msg === '0') {
                $chatEmpty = 1;
            }
            if (!$chatEmpty) {
                if ($userr) {
                    $userr->count = (string)$individualCount->count;
                    $userr->last_message = $msgp;
                    $userr->last_msg_time_ago = $tmp;
                    $userr->chat_id = (int)$chatID;
                    $userr->chatEmpty = (int)$chatEmpty;
                    array_push($users, $userr);
                }
            }
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Chat Agents Fetched Successfully',
            'successData' => [
                'myChats' => $users
            ]
        ], 200);
    }
    public function deleteChatConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required',
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'successData' => ['myChats' => []]
            ], 200);
        }

        ChatMessage::where('chat_id', $request->chat_id)
            ->where('deleted_user_id', NULL)
            ->update(['deleted_user_id' => $request->user_id]);

        ChatMessage::where('chat_id', $request->chat_id)
            ->where('deleted_user_id', '!=', NULL)
            ->where('deleted_user_id', '!=', $request->user_id)
            ->update(['deleted_user_id' => 0]);


        return $this->myChatUsers($request);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Chat Conversation Deleted Successfully',
            'successData' => ['myChats' => []]
        ], 200);
    }
    public function chatAgents(Request $request)
    {
        $ids = Chat::select('sender_id AS id')
            ->where('receiver_id', $request->user_id)
            ->get();

        $individualCount = ChatMessage::where('read_status', '0')
            ->whereIn('sender_id', $ids)
            ->selectRaw('count(sender_id) as count, sender_id')
            ->groupBy('sender_id')
            ->get();

        $agents = Agent::whereIn('user_id', $ids)
            ->with('user')
            ->get();

        foreach ($agents as $agent) {
            $x = 0;
            foreach ($individualCount as $count) {
                if ($count->sender_id == $agent->user_id) {
                    $agent['count'] = $count->count;
                    $x = 1;
                }
                if ($x == 0) {
                    $agent['count'] = '0';
                }
            }
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Chat Agents Fetched Successfully',
            'successData' => [
                'agents' => $agents
            ]
        ], 200);
    }
    public function getPersonalChannel(Request $request)
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
        $chat = Chat::where(array('receiver_id' => $request->receiver_id, 'sender_id' => '0'))->get()->first();
        if ($chat != []) {
            return response()->json([
                'statusCode' => 200,
                'message' => 'Personal Channel ID already created successfully against receiver_id: ' . $request->receiver_id,
                'successData' => ['personalChannel' => $chat]
            ], 200);
        } else {
            $chat = new Chat;
            $chat->channel_id = Str::random(30) . '-' . $request->receiver_id;
            $chat->receiver_id = $request->receiver_id;
            $chat->sender_id = '0';
            $chat->save();
            return response()->json([
                'statusCode' => 200,
                'message' => 'Personal Channel ID created successfully against receiver_id: ' . $request->receiver_id,
                'successData' => ['personalChannel' => $chat]
            ], 200);
        }
    }
}
