<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jelajahku.test'],
            [
                'name' => 'Admin JelajahKu',
                'phone' => '081200000001',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'wisatawan@jelajahku.test'],
            [
                'name' => 'Budi Wisatawan',
                'phone' => '081200000002',
                'password' => 'password',
                'role' => User::ROLE_CUSTOMER,
            ]
        );
    }
}
