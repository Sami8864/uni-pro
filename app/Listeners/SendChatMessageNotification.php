<?php

namespace App\Listeners;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendChatMessageNotification
{
    /**
     * Create the event listener.
     */

    public string $message;

    
    public function __construct( Message $message)
    {
        $this->message = $message;
       
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
    }
}
