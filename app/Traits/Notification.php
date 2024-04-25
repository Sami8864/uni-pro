<?php

namespace App\Traits;

use App\Models\Device;
use App\Models\User;

trait Notification{

    public static function token($token)
    {
   
        if (!auth()->user()->hasDevice($token)) {
            auth()->user()->devices()->create(['device_token'=>$token]);
        } else{
            auth()->user()->update(['device_token'=>$token]);
        }
        return true;
    }

    public static function send($title, $body, $tokens ,$data , $muted ,$type)
    {
        $firebaseToken =$tokens;

        $SERVER_API_KEY = 'AAAA6vkoDvU:APA91bGMdo0D2jHbhWv_9_EzA3LXygVTsLAb-rV7ADUyS2AOLSECXL1jbk0OtfbBNFKZnHk9zVPS7NhGCsr7wRUOhQ1CrKCDHrj66VLAtoy7IE1PvyGV5DIdwd6kAL_JcghPwkoTvh-c';

        $data = [
            "registration_ids" => $firebaseToken,
            "type" => $type,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "data" => $data,
                "muted"=>$muted,
                "type" => $type,
            ],
            "data" => [
                "data" =>$data,
                "type" => $type,
                "data" => $data,
                "contents" => "http://www.news-magazine.com/world-week/21659772",
                "Nick" => "Mario",
                "body" => "great match!",
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        
        return $response;
    }
}
