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

test('paid event has banner image media collection', function () {
    $paidEvent = PaidEvent::factory()->create();

    expect($paidEvent)->toBeInstanceOf(\Spatie\MediaLibrary\HasMedia::class);

    // Verify we can work with the banner_image collection
    expect($paidEvent->getMedia('banner_image')->count())->toBe(0);
});

test('paid event has tshirt size guideline media collection', function () {
    $paidEvent = PaidEvent::factory()->create();

    expect($paidEvent)->toBeInstanceOf(\Spatie\MediaLibrary\HasMedia::class);

    // Verify we can work with the tshirt_size_guideline collection
    expect($paidEvent->getMedia('tshirt_size_guideline')->count())->toBe(0);
});

test('paid event has registration fee', function () {
    $paidEvent = PaidEvent::factory()->create([
        'registration_fee' => 500.50,
    ]);

    expect($paidEvent->registration_fee)->toBe('500.50');
});

test('paid event has registration form configuration fields', function () {
    $paidEvent = PaidEvent::factory()->create([
        'student_id_rules' => 'regex:/^[0-9-]+$/',
        'student_id_rules_guide' => 'Student ID must contain only numbers and dashes',
        'pickup_points' => [
            ['name' => 'Main Gate'],
            ['name' => 'Library'],
        ],
        'departments' => [
            ['name' => 'CSE'],
            ['name' => 'EEE'],
        ],
        'sections' => [
            ['name' => 'A'],
            ['name' => 'B'],
        ],
        'lab_teacher_names' => [
            ['initial' => 'ABC', 'full_name' => 'Dr. John Doe'],
        ],
    ]);

    expect($paidEvent->student_id_rules)->toBe('regex:/^[0-9-]+$/')
        ->and($paidEvent->student_id_rules_guide)->toContain('Student ID')
        ->and($paidEvent->pickup_points)->toHaveCount(2)
        ->and($paidEvent->departments)->toHaveCount(2)
        ->and($paidEvent->sections)->toHaveCount(2)
        ->and($paidEvent->lab_teacher_names)->toHaveCount(1)
        ->and($paidEvent->lab_teacher_names[0]['initial'])->toBe('ABC');
});
