<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('unions')->insert([
            [
                'name' => 'DGA'
            ], [

                'name' => 'PGA'
            ], [

                'name' => 'WGA'
            ], [

                'name' => 'SAG-AFTRA'
            ], [

                'name' => 'SOC'
            ]
        ]);
    }
}
