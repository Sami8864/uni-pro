<?php

namespace App\Http\Resources;

use App\Models\Message;
use App\Models\FilmMaker;
use App\Models\Headshots;
use App\Models\Conversation;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function getPrimaryHeadshot(int $id)
    {
        $headshot = Headshots::where('device_id', $id)->where('type_id', 2)->value('url');
        return $headshot;
    }
    public function toArray($request)
    {
        $userId = $request->user()->id;
        if ($request->user()->hasRole('filmmaker')) {
            $user_id = User::where('id', $this->id)->value('profileprogess_id');
            $reciver_muted = Conversation::where('sender_id', $this->id)->where('receiver_id', $userId)->value('receiver_muted');
            $sender_muted = Conversation::where('receiver_id', $this->id)->where('sender_id', $userId)->value('sender_muted');
            $profile_image  = $this->getPrimaryHeadshot($user_id);
            $rs = [$this->id, $userId];
            // dump($this->lastest_message['created_at']);
            return [
                'id' => $this->id,
                'name' => UserDetail::where('device_id', $user_id )->value('name'),
                'latest_message' => isset($this->lastest_message->message) ? $this->lastest_message->message : null,
                'created_at' => $this->lastest_message !== null ? $this->lastest_message['created_at'] : 'null',
                'unread_count' => isset($this->unread_count) ? $this->unread_count : null,
                'blocked_by' => isset($this->blocked_by) ? $this->blocked_by : null,
                'profile_image' =>   isset($profile_image) ? $profile_image : 'https://cdn.quasar.dev/team/razvan_stoenescu.jpeg',
                'muted' => ($reciver_muted  ?    $reciver_muted  :  $sender_muted) ? $reciver_muted  ?    $reciver_muted  :  $sender_muted : False,
                'channel_id' => Conversation::whereIn('sender_id', $rs)->whereIn('receiver_id', $rs)->value('channel_id')
            ];
            // 210 
            // 71 
        } else {
            $profile_image = FilmMaker::where('user_id', $this->id)->value('profile_image');

            $reciver_muted = Conversation::where('sender_id', $this->id)->where('receiver_id', $userId)->value('receiver_muted');
            $sender_muted = Conversation::where('receiver_id', $this->id)->where('sender_id', $userId)->value('sender_muted');
            $rs = [$this->id, $userId];
            return [
                'id' => $this->id,
                'name' => FilmMaker::where('user_id', $this->id)->value('full_name'),
                'company_name' =>  FilmMaker::where('user_id', $this->id)->value('compnay_name'),
                'latest_message' => isset($this->lastest_message->message) ? $this->lastest_message->message : null,
                'created_at' => $this->lastest_message !== null ? $this->lastest_message['created_at']  : 'null',
                'unread_count' => isset($this->unread_count) ? $this->unread_count : null,
                'blocked_by' => isset($this->blocked_by) ? $this->blocked_by : null,
                'profile_image' => isset($profile_image) ? $profile_image : 'https://cdn.quasar.dev/team/razvan_stoenescu.jpeg',
                'muted' => ($reciver_muted  ?    $reciver_muted  :  $sender_muted) ? $reciver_muted  ?    $reciver_muted  :  $sender_muted : False,
                'channel_id' => Conversation::whereIn('sender_id', $rs)->whereIn('receiver_id', $rs)->value('channel_id')
            ];
        }
    }
}
