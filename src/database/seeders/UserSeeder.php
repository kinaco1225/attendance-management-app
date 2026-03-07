<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    User::create([
      'name' => '管理者',
      'email' => 'admin@example.com',
      'password' => Hash::make('password'),
      'role' => 'admin',
      'employee_number' => 'A0001',
    ]);

    User::create([
      'name' => '山田 太郎',
      'email' => 'user1@example.com',
      'email_verified_at' => now(),
      'password' => Hash::make('password'),
      'role' => 'user',
      'employee_number' => 'U0001',
    ]);

    User::create([
      'name' => '佐藤 花子',
      'email' => 'user2@example.com',
      'email_verified_at' => now(),
      'password' => Hash::make('password'),
      'role' => 'user',
      'employee_number' => 'U0002',
    ]);

    User::create([
      'name' => '鈴木 一郎',
      'email' => 'user3@example.com',
      'email_verified_at' => now(),
      'password' => Hash::make('password'),
      'role' => 'user',
      'employee_number' => 'U0003',
    ]);
  }
}
