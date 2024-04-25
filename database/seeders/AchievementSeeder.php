<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('achievements')->insert([
            [
                'name' => 'Noob',
                'description' => 'Noob is always  Pro',
                'points_required' => 100,
                'type' => 'types',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434529.png'
            ], [
                'name' => 'Pro',
                'description' => 'Noob is always  Pro',
                'points_required' => 1000,
                'type' => 'types',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434447.png'
            ], [
                'name' => 'Master',
                'description' => 'Noob is always  Pro',
                'points_required' => 10000,
                'type' => 'types',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434511.png'
            ], [
                'name' => 'Noob',
                'description' => 'Noob is always  Pro',
                'points_required' => 100,
                'type' => 'invites',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434529.png'
            ], [
                'name' => 'Pro',
                'description' => 'Noob is always  Pro',
                'points_required' => 1000,
                'type' => 'invites',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434447.png'
            ], [
                'name' => 'Master',
                'description' => 'Noob is always  Pro',
                'points_required' => 10000,
                'type' => 'invites',
                'award_image'=>'https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/public/achievements/1713434511.png'
            ]
        ]);
    }
}
