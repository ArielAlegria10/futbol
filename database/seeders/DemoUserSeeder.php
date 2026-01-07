<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'demo@demo.com'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('demo1234'),
            ]
        );
    }
}
