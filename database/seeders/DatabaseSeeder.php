<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UnionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        // Seed ImageType

        $this->call(PointSeeder::class);

        $this->call(ImageTypeSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);

        $this->call(EssenceSeeder::class);
        $this->call(AdvertisementSeeder::class);
        $this->call(UserTypeSeeder::class);

        $this->call(PhysiqueSeeder::class);
        $this->call(FlagSeeder::class);
        $this->call(AttributeSeeder::class);
        $this->call(UnionSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(AdminSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(AchievementSeeder::class);

        //$this->call(LinkSeeder::class);
      //  $this->call(SurveyDataSeeder::class);
    }
}
