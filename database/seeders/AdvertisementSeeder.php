<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('advertisements')->insert([
        [
            'description'=>'abc',
            'name'=>'abc',
            'video_url'=>'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
            'status'=>false
        ]
        ,[
            'description'=>'abc',
            'name'=>'abc',
            'video_url'=>'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4',
            'status'=>false
        ]
        ,[
            'description'=>'abc',
            'name'=>'abc',
            'video_url'=>'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'status'=>true
        ]
        ]);
    }
}
