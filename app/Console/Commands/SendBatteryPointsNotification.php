<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Device;
use App\Models\Headshots;
use Illuminate\Support\Str;
use App\Models\ProfileProgress;
use Illuminate\Console\Command;
use App\Notifications\DailyReportNotification;
use App\Traits\Notification as NotificationTrait;
use App\Models\Notification as NotificationModel; // Alias the Notification model

class SendBatteryPointsNotification extends Command
{
    use NotificationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-battery-points-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    
    public function handle()
    {
        // Fetch daily report data, for example, all users
        $tempUsers = ProfileProgress::all();
        foreach ($tempUsers as $tempUser) {
            $array[] = User::where('profileprogess_id', $tempUser->id)->value('id');
        }
        foreach ($array as $arr) {
            $token[] = Device::where('user_id', $arr)->value('device_token');
        }
        foreach ($token as $key) {
            $id = Device::where('device_token', $key)->value('user_id');
            $user_id  = User::where('id', $id)->value('profileprogess_id');
            $point =   ProfileProgress::where('id', $user_id)->value('types_points');
            $this->send('You typed ' .  $point, 'You definitely went beast mode yesterday with the amount of photos you typed! ', [$key], $key, 'dasdas', 'dasdsa');
            $response =   [
                'title' => 'You typed ' .  $point . ' photos yesterday',
                'body' => 'You definitely went beast mode yesterday with the amount of photos you typed! ',
                'image' =>  Headshots::where('device_id', $user_id)->where('type_id', 2)->value('url'),
            ];
            if (isset($id)) {
                $notification = NotificationModel::create([
                    'id' => (string) Str::uuid(), // Generate UUID and cast to string
                    'type' => 'dailyreport', // Adjust as needed
                    'notifiable_type' => User::class, // Assuming you're associating the notification with the User model
                    'notifiable_id' => $id, // Provide the actual id of the user
                    'data' => $response // Adjust as needed
                ]);
            }
        }
        $this->info('Daily report notifications sent successfully.');
    }
}
