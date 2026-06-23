<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'jegatha.nvlx@gmail.com')->delete();

        User::create([
    'name'         => 'Admin',
    'email'        => 'jegatha.nvlx@gmail.com',
    'password'     => Hash::make('admin123'),
    'role'         => 'admin',
    'organisation' => 'Fuiox Technologies',
    'mobile'       => '0000000000',
    'is_verified'  => true,
]);

        echo "✅ Admin seeded!\n";
    }
}