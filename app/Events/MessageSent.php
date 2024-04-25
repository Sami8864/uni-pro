<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public  $message;
    public string $channel_id;
    public $muted;
    public function __construct($channel_id ,$muted,$message)
    {
        $this->channel_id = $channel_id;
        $this->muted = $muted;
        $this->message = $message;

       
    }
   
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    // public function broadcastOn()
    // {
    //     return new Channel('chat');
    // }
    public function broadcastOn()
    {
        return new PrivateChannel("userChat.{$this->channel_id}");
    }
    public function broadcastAs(){
        return "users.chat";
    }
}
