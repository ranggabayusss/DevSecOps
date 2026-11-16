<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) env('INITIAL_ADMIN_NAME', 'Administrator'));
        $email = strtolower(trim((string) env('INITIAL_ADMIN_EMAIL', '')));
        $password = (string) env('INITIAL_ADMIN_PASSWORD', '');

        if ($email === '' || $password === '' || strlen($password) < 12) {
            throw new RuntimeException(
                'Set INITIAL_ADMIN_EMAIL and a password of at least 12 characters in .env before running AdminUserSeeder.'
            );
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->save();
    }
}
