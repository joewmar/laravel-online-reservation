<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\System>
 */
class SystemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'contact' => fake()->e164PhoneNumber(),
            'email' => fake()->unique()->freeEmail(),
            'email_verified_at' => now(),
            'username' => fake()->userName(), // password
            'password' => bcrypt(fake()->password()), // password
            'type' => fake()->numberBetween(0, 2), // password
            'passcode' => bcrypt(fake()->randomNumber(4, true)), // password
            'telegram_username' =>fake()->userName(), // password
            'telegram_chatID' => fake()->randomNumber(9, true), // password
            'remember_token' => Str::random(10),
        ];
        
    }
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
