<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::create([
      'username' => 'admin',
      'email' => 'admin@email.com',
      'password' => Hash::make('admin123'),
      'role_id' => Role::where('name', 'admin')->first()->id,
      'is_verified' => true
    ]);

    User::create([
      'username' => 'user',
      'email' => 'user@email.com',
      'password' => Hash::make('user123'),
      'role_id' => Role::where('name', 'user')->first()->id,
      'is_verified' => true
    ]);
  }
}
