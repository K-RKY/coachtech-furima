<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '山田太郎',
            'email' => 'yamada@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => '鈴木次郎',
            'email' => 'suzuki@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => '佐藤三郎',
            'email' => 'sato@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => '田中四郎',
            'email' => 'tanaka@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => '小林花子',
            'email' => 'kobayashi@test.com',
            'password' => Hash::make('password'),
        ]);
    }
}
