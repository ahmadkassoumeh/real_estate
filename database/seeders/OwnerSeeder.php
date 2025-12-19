<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::create([
            'email' => 'owner@test.com',
            'username' => 'owner1',
            'first_name' => 'Owner',
            'last_name' => 'User',
            'password' => Hash::make('123456'),
        ]);

        $owner->assignRole('owner');
    }
}
