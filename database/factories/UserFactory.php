<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'no_hp' => fake()->unique()->numerify('08#########'),
            'pin' => '$2a$12$wO524Q6yewzSWHdEbk1oA.L.9pbvfEHxNxRFJz4LtgnV03mge4Xj2', // 2233
            'tipe' => fake()->randomElement(['B', 'S']),
            'remember_token' => Str::random(10),
        ];
    }
}
