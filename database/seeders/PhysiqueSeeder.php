<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PhysiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data=[
                ['name'=>'Slender'],['name'=>'Athletic'],['name'=>'HeavySet']
        ];
        DB::table('physiques')->insert($data);
    }
}
