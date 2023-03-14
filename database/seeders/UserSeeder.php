<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'no_hp' => '000',
                'nama' => 'Admin',
                'pin' => '$2a$12$MhPt9ulTF0LpERO3/faRKuAe3WYFnpz.kCjP4yOebbSUkZ6Jy/91C', // 0000
                'tipe' => 'A'
            ],
        ]);
    }
}
