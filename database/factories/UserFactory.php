<?php

namespace Database\Factories;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserType::USER->value,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserType::USER->value,
        ]);
    }

    public function merchant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserType::MERCHANT->value,
            'business_name' => fake()->company(),
            'business_activity' => 'تجزئة',
            'business_location' => fake()->city(),
            'tax_number' => fake()->numerify('3##########'),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserType::ADMIN->value,
        ]);
    }
}
