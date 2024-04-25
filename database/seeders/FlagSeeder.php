<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('flag_types')->insert([
            [
                'name'=>'Sexuality or Nudity'
            ],
            [
                'name'=>'Hate Speech or Symbols'
            ],
            [
                'name'=>'Violence or Dangerous Organization'
            ],
            [
                'name'=>'Suicide or Self threatening'
            ],
            [
                'name'=>'Fraud or promotional'
            ]
            ]);
    }
}
