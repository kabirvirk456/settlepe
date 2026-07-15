<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => User::ROLE_CUSTOMER,
            'mobile' => fake()->unique()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'age' => fake()->numberBetween(24, 60),
            'gender' => fake()->randomElement(['male', 'female', 'non_binary', 'prefer_not_to_say']),
            'pan_card' => strtoupper(fake()->bothify('?????####?')),
            'income' => fake()->numberBetween(30000, 250000),
            'cibil_profile_completed_at' => now(),
            'sales_status' => 'cibil_fetched',
            'priority' => 'normal',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
