<?php

namespace App\Http\Controllers;

use App\Models\PaidEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PaidEventController extends Controller
{
    public function index(Request $request): Response
    {
        $paidEvents = PaidEvent::query()
            ->select([
                'id',
                'title',
                'slug',
                'semester',
                'registration_deadline',
                'registration_start_time',
                'registration_limit',
                'registration_fee',
                'status',
            ])
            ->published()
            ->search($request->get('search'))
            ->ofStatus($request->get('status'))
            ->withCount('registrations')
            ->orderBy('registration_deadline', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Add banner image URLs to each paid event
        $paidEvents->getCollection()->transform(function ($paidEvent) {
            $paidEvent->banner_image_url = $paidEvent->getFirstMediaUrl('banner_image', 'banner');

            return $paidEvent;
        });

        // Hide fields that were only needed for query scopes
        $paidEvents->getCollection()->makeHidden(['status']);

        $search = $request->get('search');
        $seoDescription = $search
            ? "Search results for '{$search}' in DIU ACM paid events. Find registrations for bus trips, tours, and special events."
            : 'Discover upcoming DIU ACM paid events including bus trips, tours, and special activities. Register online with ease.';

        return Inertia::render('paid-events/index', [
            'paidEvents' => $paidEvents,
            'filters' => [
                'search' => $request->get('search'),
            ],
        ])->withViewData([
            'SEOData' => new SEOData(
                title: $search ? "Search: {$search}" : 'Paid Events',
                description: $seoDescription,
            ),
        ]);
    }

    public function show(PaidEvent $paidEvent): Response
    {
        // Only show published paid events
        if ($paidEvent->status !== 'published') {
            abort(404);
        }

        // Load the paid event with all needed fields
        $paidEvent = PaidEvent::query()
            ->where('id', $paidEvent->id)
            ->where('status', 'published')
            ->firstOrFail();

        // Get registration statistics
        $totalRegistrations = $paidEvent->registrations()->count();
        $confirmedRegistrations = $paidEvent->registrations()->confirmed()->count();

        // Check if registration is open
        $isRegistrationOpen = $paidEvent->isRegistrationOpen();
        $isRegistrationFull = $paidEvent->isRegistrationFull();

        // Get banner image URL
        $bannerImageUrl = $paidEvent->getFirstMediaUrl('banner_image', 'banner');

        return Inertia::render('paid-events/show', [
            'paidEvent' => [
                'id' => $paidEvent->id,
                'title' => $paidEvent->title,
                'slug' => $paidEvent->slug,
                'semester' => $paidEvent->semester,
                'description' => $paidEvent->description,
                'registration_deadline' => $paidEvent->registration_deadline?->toISOString(),
                'registration_start_time' => $paidEvent->registration_start_time?->toISOString(),
                'registration_limit' => $paidEvent->registration_limit,
                'registration_fee' => $paidEvent->registration_fee,
                'student_id_rules' => $paidEvent->student_id_rules,
                'student_id_rules_guide' => $paidEvent->student_id_rules_guide,
                'pickup_points' => $paidEvent->pickup_points,
                'departments' => $paidEvent->departments,
                'sections' => $paidEvent->sections,
                'lab_teacher_names' => $paidEvent->lab_teacher_names,
                'banner_image_url' => $bannerImageUrl,
            ],
            'registrationInfo' => [
                'is_open' => $isRegistrationOpen,
                'is_full' => $isRegistrationFull,
                'total_registrations' => $totalRegistrations,
                'confirmed_registrations' => $confirmedRegistrations,
            ],
        ])->withViewData([
            'SEOData' => new SEOData(
                title: $paidEvent->title,
                description: $paidEvent->description ?? "Register for {$paidEvent->title} - DIU ACM",
            ),
        ]);
    }
}
