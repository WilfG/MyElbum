<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PlanSeeder extends Seeder
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
            'plan_title' => $faker->title(8),
            'duration_time' => $faker->randomNumber(5),
            'storage_capacity' => $faker->randomElement(['8', '16', '32', '64'])
        ]);
    }
}
