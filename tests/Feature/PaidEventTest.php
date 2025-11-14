<?php

use App\Models\PaidEvent;

test('can create paid event', function () {
    $paidEvent = PaidEvent::factory()->create([
        'title' => 'Test Paid Event',
        'status' => 'published',
    ]);

    expect($paidEvent->title)->toBe('Test Paid Event')
        ->and($paidEvent->status)->toBe('published')
        ->and($paidEvent->slug)->not->toBeNull();
});

test('published scope returns only published events', function () {
    PaidEvent::factory()->create(['status' => 'published']);
    PaidEvent::factory()->create(['status' => 'draft']);
    PaidEvent::factory()->create(['status' => 'closed']);

    $published = PaidEvent::published()->get();

    expect($published)->toHaveCount(1)
        ->and($published->first()->status)->toBe('published');
});

test('search scope filters by title, semester, and slug', function () {
    PaidEvent::factory()->create(['title' => 'Spring Programming Contest', 'semester' => 'Spring 2024']);
    PaidEvent::factory()->create(['title' => 'Fall Hackathon', 'semester' => 'Fall 2024']);

    $results = PaidEvent::search('Spring')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toContain('Spring');
});

test('registration is open when status is published and within time range', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->subDay(),
        'registration_deadline' => now()->addDay(),
    ]);

    expect($paidEvent->isRegistrationOpen())->toBeTrue();
});

test('registration is closed when status is not published', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'draft',
        'registration_start_time' => now()->subDay(),
        'registration_deadline' => now()->addDay(),
    ]);

    expect($paidEvent->isRegistrationOpen())->toBeFalse();
});

test('registration is closed when outside time range', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->addDay(),
        'registration_deadline' => now()->addDays(2),
    ]);

    expect($paidEvent->isRegistrationOpen())->toBeFalse();
});
