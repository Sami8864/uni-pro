<?php

namespace Database\Seeders;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class PointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("point_types")->insert([
            ['type' => 'Swiper_add','points'=>10],
            ['type' => 'Swiper_del','points'=>1],
            ['type' => 'Image','points'=>50],
            ['type' => 'Reel','points'=>100],
            ['type' => 'Achievement','points'=>250]
        ]);
    }
}
