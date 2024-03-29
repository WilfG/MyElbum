<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        DB::table('plans')->insert([
            'name' => $faker->name(),
            'email' => $faker->email(),
            'phone_number' => $faker->phoneNumber(15),
            'user_id' => 1
        ]);
    }
}
