<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'DGA',

            ],
            [
                'name' => 'PGA',

            ]
            ,
            [
                'name' => 'AFTRA',

            ],
            [
                'name' => 'SOC',
            ]
        ];
        DB::table('unions')->insert($data);
    }
}
