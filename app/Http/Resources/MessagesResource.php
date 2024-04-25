<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
    
        return [
            'id' => $this->id,
            'message' => $this->message,
            'conversation_id' => $this->conversation_id,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'sender' => new MessageSenderResource($this->sender),
            'receiver_id' => $this->receiver_id,
        ];
    }
}
