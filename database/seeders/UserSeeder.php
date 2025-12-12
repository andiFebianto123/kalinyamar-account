<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'andifebianto@gmail.com', // key pencarian
                'data' => [
                    'name' => 'Andi Febian',
                    'profile_photo' => 'default.png',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('1234567890'),
                    'no_order' => 1,
                    //'remember_token' => Str::random(10),
                ]
            ],
            [
                'email' => 'user2@example.com',
                'data' => [
                    'name' => 'User Kedua',
                    'profile_photo' => null,
                    'email_verified_at' => null,
                    'password' => Hash::make('password123'),
                    'no_order' => 2,
                    // 'remember_token' => Str::random(10),
                ]
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],   // kondisi unique
                $user['data'] + [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $userAdmin = User::where("email", "andifebianto@gmail.com")->first();
        if($userAdmin->hasRole('Super Admin') == false){
            $userAdmin->assignRole('Super Admin');
        }

    }
}
