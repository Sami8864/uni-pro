<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ai_attributes')->insert([
            [
                'name' => 'age'
            ], [

                'name' => 'gender'
            ], [

                'name' => 'race'
            ], [

                'name' => 'profession'
            ], [

                'name' => 'essence'
            ]
        ]);
    }
}
