<?php

namespace Database\Seeders;

use App\Models\Type;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userTypes = [
            'bad bitch',
            'law enforcement',
            'top boss',
            'tech guru',
            'creative genius',
            'adventure seeker',
            'fitness enthusiast',
            'foodie',
            'music lover',
            'bookworm',
            'travel addict',
            'animal lover',
            'fashionista',
            'entrepreneur',
            'gamer',
            'movie buff',
            'coffee addict',
            'social activist',
            'photography lover',
            'yoga practitioner',
            'art connoisseur',
            'science geek',
            'plant parent',
            'DIY enthusiast',
            'language learner',
            'history buff',
            'podcast aficionado',
            // Add more user types as needed
        ];

        foreach ($userTypes as $userType) {
            Type::create(['name' => $userType]);
        }
    }
}
