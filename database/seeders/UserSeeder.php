<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::whereIn('email', [
            'management@test.com',
            'uploader@test.com'
        ])->delete();

        User::create([
            'name'  => 'Management',
            'email'  => 'management@test.com',
            'password'  => Hash::make('password123'),
            'role'  => 0,
        ]);

        User::create([
            'name'  => 'Uploader',
            'email'  => 'uploader@test.com',
            'password'  => Hash::make('password456'),
            'role'  => 1,
        ]);
    }
}
