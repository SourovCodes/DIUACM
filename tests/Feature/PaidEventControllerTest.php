<?php

use App\Models\PaidEvent;
use App\Models\PaidEventRegistration;

use function Pest\Laravel\get;

it('loads paid events index page with only required fields', function () {
    // Create test paid events
    $paidEvents = PaidEvent::factory()->count(3)->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    // Create some registrations for the first paid event
    PaidEventRegistration::factory()->count(2)->create([
        'paid_event_id' => $paidEvents->first()->id,
    ]);

    $response = get('/paid-events');

    $response->assertSuccessful();

    // Verify the response contains the paid events
    $response->assertInertia(fn ($page) => $page->component('paid-events/index')
        ->has('paidEvents.data', 3)
        ->has('paidEvents.data.0.banner_image_url')
        ->has('filters')
    );
});

it('can search paid events by title', function () {
    PaidEvent::factory()->create([
        'title' => 'Bus Trip to Cox\'s Bazar',
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    PaidEvent::factory()->create([
        'title' => 'Campus Tour',
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get('/paid-events?search=Bus');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->has('paidEvents.data', 1)
        ->where('filters.search', 'Bus')
    );
});

it('can search paid events by semester', function () {
    PaidEvent::factory()->create([
        'title' => 'Spring 2024 Trip',
        'semester' => 'Spring 2024',
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    PaidEvent::factory()->create([
        'title' => 'Fall 2024 Trip',
        'semester' => 'Fall 2024',
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get('/paid-events?search=Spring');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->has('paidEvents.data', 1)
    );
});

it('only shows published paid events', function () {
    PaidEvent::factory()->count(2)->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    PaidEvent::factory()->create([
        'status' => 'draft',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get('/paid-events');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->has('paidEvents.data', 2) // Should only find 2 published paid events
    );
});

it('can paginate paid events', function () {
    PaidEvent::factory()->count(15)->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get('/paid-events?page=2');

    $response->assertSuccessful();
    $response->assertInertia(function ($page) {
        $page->component('paid-events/index')
            ->has('paidEvents.data')
            ->where('paidEvents.current_page', 2);
    });
});

// Paid Event Details Page Tests

it('shows paid event details page for published events', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'title' => 'Test Paid Event',
        'description' => 'Test Description',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('paid-events/show')
        ->has('paidEvent')
        ->where('paidEvent.title', 'Test Paid Event')
        ->has('registrationInfo')
    );
});

it('returns 404 for draft paid events', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'draft',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertNotFound();
});

it('shows registration info correctly', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
        'registration_limit' => 50,
    ]);

    // Create some confirmed registrations
    PaidEventRegistration::factory()->count(10)->create([
        'paid_event_id' => $paidEvent->id,
        'status' => \App\Enums\RegistrationStatus::CONFIRMED,
    ]);

    // Create some pending registrations
    PaidEventRegistration::factory()->count(5)->create([
        'paid_event_id' => $paidEvent->id,
        'status' => \App\Enums\RegistrationStatus::PENDING,
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('paid-events/show')
        ->where('registrationInfo.is_open', true)
        ->where('registrationInfo.is_full', false)
        ->where('registrationInfo.total_registrations', 15)
        ->where('registrationInfo.confirmed_registrations', 10)
    );
});

it('shows registration as full when limit is reached', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
        'registration_limit' => 10,
    ]);

    // Create confirmed registrations up to the limit
    PaidEventRegistration::factory()->count(10)->create([
        'paid_event_id' => $paidEvent->id,
        'status' => \App\Enums\RegistrationStatus::CONFIRMED,
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('paid-events/show')
        ->where('registrationInfo.is_full', true)
    );
});

it('shows registration as closed when deadline passed', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(10),
        'registration_deadline' => now()->subDays(1),
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('paid-events/show')
        ->where('registrationInfo.is_open', false)
    );
});

it('includes all paid event details', function () {
    $paidEvent = PaidEvent::factory()->create([
        'status' => 'published',
        'registration_start_time' => now()->subDays(1),
        'registration_deadline' => now()->addDays(7),
        'pickup_points' => ['Dhaka', 'Chittagong'],
        'departments' => ['CSE', 'EEE'],
        'sections' => ['A', 'B'],
        'lab_teacher_names' => ['Teacher A', 'Teacher B'],
    ]);

    $response = get("/paid-events/{$paidEvent->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('paid-events/show')
        ->has('paidEvent.pickup_points')
        ->has('paidEvent.departments')
        ->has('paidEvent.sections')
        ->has('paidEvent.lab_teacher_names')
    );
});
