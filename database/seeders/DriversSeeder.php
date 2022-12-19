<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriversSeeder extends Seeder
{

    public function run()
    {
        $faker = \Faker\Factory::create('pt_PT');

        $this->command->info("Drivers seeder - Start");

        $users = DB::table('users')->select('id', 'created_at', 'updated_at', 'deleted_at')
            ->where('type', '=', "ED")->get()->toArray();

        foreach ($users as $user) {
            if (rand(0, 2) == 1) {
                DB::table('drivers')->insert([
                    'user_id' => $user->id,
                    'phone' => $faker->phoneNumber,
                    'license_plate' => $faker->regexify('[A-Z0-9]{2}-[A-Z0-9]{2}-[A-Z0-9]{2}'),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => $user->deleted_at,
                ]);
            }
        }
    }
}
