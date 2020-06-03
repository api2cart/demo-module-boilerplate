<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = \DB::table('users')->count();
        if($count == 0) {

            User::create([
                'name'              => 'Admin',
                'email'             => 'admin@local.com',
                'password'          => Hash::make('123456'),
                'email_verified_at' => Carbon::now(),
                'api2cart_key'      => 'f408de7875733736c244b75a4f33862a',
                'api2cart_verified' => true,
            ]);

        }


    }
}
