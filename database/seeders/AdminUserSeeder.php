<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Enums\UserStatusEnum;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء role admin إذا غير موجود
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // إنشاء المستخدم
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username'   => 'admin',
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'password'   => Hash::make('password'),
                'status'     => UserStatusEnum::APPROVED,
            ]
        );

        // إسناد الدور
        if (! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}
