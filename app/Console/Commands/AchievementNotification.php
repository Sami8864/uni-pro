<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Achievement;
use App\Models\ProfileProgress;
use App\Models\UserAchievement;
use Illuminate\Console\Command;

class AchievementNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:achievement-notification';

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
        $users = ProfileProgress::all();
        foreach ($users as $user) {
            $achievements = UserAchievement::where('profile_progress_id', $user->id)
                ->whereDate('created_at', today()) // Filter achievements for the current day
                ->get();

             dd( $achievements );
            if ($achievements->isNotEmpty()) {
                // Send notification to the user
                $achievementDetails = Achievement::whereIn('id', $achievements->pluck('achievement_id'))->get();
             //   $user->notify(new DailyAchievementsNotification($achievements));
            }
        }
    }
}
