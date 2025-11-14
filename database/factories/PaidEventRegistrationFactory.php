<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use App\Models\PaidEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaidEventRegistration>
 */
class PaidEventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transportRequired = fake()->boolean(30); // 30% chance of requiring transport

        return [
            'paid_event_id' => PaidEvent::factory(),
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'student_id' => fake()->numerify('###-##-####'),
            'phone' => fake()->numerify('01#########'),
            'section' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'PC-A', 'PC-B']),
            'department' => fake()->randomElement(['CSE', 'SWE', 'EEE', 'CE', 'BBA', 'ENG']),
            'lab_teacher_name' => fake()->name(),
            'tshirt_size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL']),
            'gender' => fake()->randomElement(Gender::cases()),
            'transport_service_required' => $transportRequired,
            'pickup_point' => $transportRequired ? fake()->randomElement(['Motijheel', 'Uttara', 'Mirpur', 'Mohakhali', 'Dhanmondi']) : null,
            'amount' => fake()->randomFloat(2, 100, 2000),
            'payment_method' => PaymentMethod::SSLCOMMERZ,
            'status' => fake()->randomElement(RegistrationStatus::cases()),
        ];
    }

    /**
     * Indicate that the registration is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::CONFIRMED,
        ]);
    }

    /**
     * Indicate that the registration is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the registration is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::CANCELLED,
        ]);
    }

    /**
     * Indicate that the registration requires transport.
     */
    public function withTransport(): static
    {
        return $this->state(fn (array $attributes) => [
            'transport_service_required' => true,
            'pickup_point' => fake()->randomElement(['Motijheel', 'Uttara', 'Mirpur', 'Mohakhali', 'Dhanmondi']),
        ]);
    }

    /**
     * Indicate that the registration does not require transport.
     */
    public function withoutTransport(): static
    {
        return $this->state(fn (array $attributes) => [
            'transport_service_required' => false,
            'pickup_point' => null,
        ]);
    }
}
