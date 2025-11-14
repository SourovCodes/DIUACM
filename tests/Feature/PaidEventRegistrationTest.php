<?php

use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use App\Models\PaidEvent;
use App\Models\PaidEventRegistration;
use App\Models\User;

test('a user can register for a paid event', function () {
    $user = User::factory()->create();
    $paidEvent = PaidEvent::factory()->create(['registration_fee' => 500.00]);

    $registration = PaidEventRegistration::factory()->create([
        'user_id' => $user->id,
        'paid_event_id' => $paidEvent->id,
        'amount' => 500.00,
    ]);

    expect($registration->user->id)->toBe($user->id)
        ->and($registration->paidEvent->id)->toBe($paidEvent->id)
        ->and($registration->amount)->toBe('500.00');
});

test('a user can only register once for a paid event', function () {
    $user = User::factory()->create();
    $paidEvent = PaidEvent::factory()->create();

    PaidEventRegistration::factory()->create([
        'user_id' => $user->id,
        'paid_event_id' => $paidEvent->id,
    ]);

    $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

    PaidEventRegistration::factory()->create([
        'user_id' => $user->id,
        'paid_event_id' => $paidEvent->id,
    ]);
});

test('paid event has many registrations', function () {
    $paidEvent = PaidEvent::factory()->create();

    PaidEventRegistration::factory()->count(3)->create([
        'paid_event_id' => $paidEvent->id,
    ]);

    expect($paidEvent->registrations)->toHaveCount(3);
});

test('user has many paid event registrations', function () {
    $user = User::factory()->create();

    PaidEventRegistration::factory()->count(2)->create([
        'user_id' => $user->id,
    ]);

    expect($user->paidEventRegistrations)->toHaveCount(2);
});

test('registration can be confirmed', function () {
    $registration = PaidEventRegistration::factory()->confirmed()->create();

    expect($registration->isConfirmed())->toBeTrue()
        ->and($registration->status)->toBe(RegistrationStatus::CONFIRMED);
});

test('registration can be pending', function () {
    $registration = PaidEventRegistration::factory()->pending()->create();

    expect($registration->isPending())->toBeTrue()
        ->and($registration->status)->toBe(RegistrationStatus::PENDING);
});

test('can query confirmed registrations', function () {
    PaidEventRegistration::factory()->confirmed()->count(3)->create();
    PaidEventRegistration::factory()->pending()->count(2)->create();

    $confirmedCount = PaidEventRegistration::confirmed()->count();

    expect($confirmedCount)->toBe(3);
});

test('registration checks if event is full based on confirmed registrations', function () {
    $paidEvent = PaidEvent::factory()->create([
        'registration_limit' => 2,
    ]);

    expect($paidEvent->isRegistrationFull())->toBeFalse();

    PaidEventRegistration::factory()->confirmed()->create([
        'paid_event_id' => $paidEvent->id,
    ]);

    expect($paidEvent->isRegistrationFull())->toBeFalse();

    PaidEventRegistration::factory()->confirmed()->create([
        'paid_event_id' => $paidEvent->id,
    ]);

    expect($paidEvent->fresh()->isRegistrationFull())->toBeTrue();
});

test('pending registrations do not count towards registration limit', function () {
    $paidEvent = PaidEvent::factory()->create([
        'registration_limit' => 1,
    ]);

    PaidEventRegistration::factory()->pending()->count(5)->create([
        'paid_event_id' => $paidEvent->id,
    ]);

    expect($paidEvent->isRegistrationFull())->toBeFalse();
});

test('registration with transport has pickup point', function () {
    $registration = PaidEventRegistration::factory()->withTransport()->create();

    expect($registration->transport_service_required)->toBeTrue()
        ->and($registration->pickup_point)->not->toBeNull();
});

test('registration without transport has no pickup point', function () {
    $registration = PaidEventRegistration::factory()->withoutTransport()->create();

    expect($registration->transport_service_required)->toBeFalse()
        ->and($registration->pickup_point)->toBeNull();
});

test('registration defaults to sslcommerz payment method', function () {
    $registration = PaidEventRegistration::factory()->create();

    expect($registration->payment_method)->toBe(PaymentMethod::SSLCOMMERZ);
});
