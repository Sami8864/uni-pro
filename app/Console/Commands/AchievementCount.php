<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Device;
use App\Models\Achievement;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Models\ProfileProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AchievementNotification;
use App\Traits\Notification as NotificationTrait;
use App\Models\Notification as NotificationModel;

class AchievementCount extends Command
{
    use NotificationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'achievement:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the Achievement';

    /**
     * Execute the console command.
     */
    // public function trackPointsAndAwardAchievements()
    // {
    //     try {
    //         $user = Auth::user();
    //         $profileProgress = ProfileProgress::where('id', $user->profileprogess_id)->first();

    //         if (!$profileProgress) {
    //             return response()->json(['error' => 'Profile Progress not found'], Response::HTTP_NOT_FOUND);
    //         }

    //         // Track invites_points logic
    //         // Assuming you have some logic to track invites_points here...

    //         // Get achievements that meet the criteria and haven't been achieved yet for types_points
    //         $achievementsToAttachTypes = Achievement::where('type', 'types')
    //             ->where('points_required', '<=', $profileProgress->types_points)
    //             ->whereDoesntHave('profileProgress', function ($query) use ($profileProgress) {
    //                 $query->where('profile_progress_id', $profileProgress->id);
    //             })
    //             ->get();
    //         // Get achievements that meet the criteria and haven't been achieved yet for invites_points
    //         $achievementsToAttachInvites = Achievement::where('type', 'invites')
    //             ->where('points_required', '<=', $profileProgress->invites_points)
    //             ->whereDoesntHave('profileProgress', function ($query) use ($profileProgress) {
    //                 $query->where('profile_progress_id', $profileProgress->id);
    //             })
    //             ->get();
    //         // Function to attach achievements
    //         function attachAchievements($achievements, $profileProgress)
    //         {
    //             foreach ($achievements as $achievement) {
    //                 $achievementPoints = $achievement->points_required;
    //                 $totalPointsRequired = $achievementPoints;

    //                 // Check if totalPointsRequired is greater than zero to avoid division by zero
    //                 $percentageAchieved = $totalPointsRequired > 0 ? ($profileProgress->types_points / $totalPointsRequired) * 100 : 0;

    //                 // Check if the achievement is already attached
    //                 if (!$profileProgress->achievementsWithPercentage->contains($achievement)) {
    //                     // Attach each eligible achievement with its own percentage achieved
    //                     $pivotData = ['percentage_achieved' => $percentageAchieved];
    //                     $profileProgress->achievementsWithPercentage()->attach($achievement->id, $pivotData);
    //                 }
    //             }
    //         }

    //         // Attach achievements for types_points
    //         attachAchievements($achievementsToAttachTypes, $profileProgress);

    //         // Attach achievements for invites_points
    //         attachAchievements($achievementsToAttachInvites, $profileProgress);

    //         return response()->json(['success' => 'Points tracked and achievements awarded successfully'], Response::HTTP_OK);
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
    //     }
    // }
    // public function index()
    // {

    //     $this->trackPointsAndAwardAchievements();
    //     $user = Auth::user();

    //     $profileProgress = ProfileProgress::where('id', $user->profileprogess_id)->first();

    //     if (!$profileProgress) {
    //         return response()->json(['error' => 'Profile Progress not found'], Response::HTTP_NOT_FOUND);
    //     }
    //     $achievements = Achievement::all();
    //     $unlocked = $profileProgress->achievementsWithPercentage->map(function ($achievement) {
    //         return [
    //             'id' => $achievement->id,
    //             'name' => $achievement->name,
    //             'description' => $achievement->description,
    //             'points_required' => $achievement->points_required,
    //             'type' => $achievement->type,
    //             'award_image' => $achievement->award_image,
    //             'percentage_achieved' => $achievement->pivot->percentage_achieved,
    //         ];
    //     });
    //     $locked = $achievements->diff($profileProgress->achievementsWithPercentage)->map(function ($achievement) {
    //         return [
    //             'id' => $achievement->id,
    //             'name' => $achievement->name,
    //             'description' => $achievement->description,
    //             'points_required' => $achievement->points_required,
    //             'type' => $achievement->type,
    //             'award_image' => $achievement->award_image,
    //             'percentage_achieved' => null, // Locked achievements won't have a percentage achieved
    //         ];
    //     });
    //     $data = [];
    //     $data['invites_points'] = (int)$profileProgress->invites_points;
    //     $data['types_points'] = (int) $profileProgress->types_points;
    //     $data['allAchievment'] = $achievements;
    //     $data['unlocked'] = $unlocked;
    //     $data['locked'] = $locked;
    //     // Retrieve and return all achievements


    //     return response()->json(['code' => 200, 'data' => $data], 200);
    // }
    public function handle()
    {
        //   $this->index();
        $tempUsers = ProfileProgress::all();

        foreach ($tempUsers as $tempUser) {
            $array[] = User::where('profileprogess_id', $tempUser->id)->value('id');
        }

        foreach ($array as $arr) {
            $token[] = Device::where('user_id', $arr)->latest()->pluck('device_token')->first();
        }

        foreach ($token as $key) {
            $id = Device::where('device_token', $key)->value('user_id');

            $user_id  = User::where('id', $id)->value('profileprogess_id');
            $achievement_id =  DB::table('user_achievements')->where('profile_progress_id', $user_id)->value('achievement_id');

            $achievement =  Achievement::where('id',  $achievement_id)->first();
           
            if (isset($achievement)) {
                $response =   [
                    'title' => 'You reached ' . $achievement->name,
                    'body' => 'You got to 5700 types and have reached level typeRookie!',
                    'image' => $achievement->award_image,
                    'achievement_id' => $achievement_id,
                ];
                $this->send('You typed ' . $achievement->name, 'You definitely went beast mode yesterday with the amount of photos you typed! ', ['$key'], $key,'dasdas','dasdsa');
                if (isset($id)) {
                    $notification = NotificationModel::create([
                        'id' => (string) Str::uuid(), // Generate UUID and cast to string
                        'type' => 'acievement', // Adjust as needed
                        'notifiable_type' => User::class, // Assuming you're associating the notification with the User model
                        'notifiable_id' => $id, // Provide the actual id of the user
                        'data' => $response // Adjust as needed
                    ]);
                }
            }
        }
        $this->info('Achievement  notifications sent successfully.');
    }
}
