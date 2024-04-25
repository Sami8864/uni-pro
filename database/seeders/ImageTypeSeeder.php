<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ImageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('image_types')->insert(
            [
                [
                      'name'=>'survey_image'
                ],
                [
                    'name'=>'primary_image'
                ],
                [
                    'name'=>'regular_image'
                ],
             ]
        );
    }
}
