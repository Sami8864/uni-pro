<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
class AdminSeeder extends Seeder
{
    protected $password='1234';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
            'email'=>'admin@doe.com',
            'password'=>Hash::make($this->password),
            'user_type'=>'admin',
            'name'=>'John Doe'
        ]);
    }
}
