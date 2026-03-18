<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        User::create([
            'name' => 'Admin2',
            'email' => 'admin2@admin.com',
            'password' => bcrypt('admin2'),
        ]);
    }
}
