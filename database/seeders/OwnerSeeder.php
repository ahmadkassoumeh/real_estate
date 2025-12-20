<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserStatusEnum;

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
            'status'     => UserStatusEnum::APPROVED,

        ]);

        $owner->assignRole('owner');
    }
}
