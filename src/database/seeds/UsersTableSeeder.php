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

        User::create([
            'name'              => 'Admin',
            'email'             => 'admin@local.com',
            'password'          => Hash::make('123456'),
            'email_verified_at' => Carbon::now(),
        ]);

    }
}
