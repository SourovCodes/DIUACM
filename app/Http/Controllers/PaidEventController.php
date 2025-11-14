<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Enums\RegistrationStatus;
use App\Models\PaidEvent;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function register(PaidEvent $paidEvent): Response|RedirectResponse
    {
        // Only allow registration for published paid events
        if ($paidEvent->status !== 'published') {
            abort(404);
        }

        // Check if registration is open
        if (! $paidEvent->isRegistrationOpen()) {
            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('error', 'Registration is not currently open for this event.');
        }

        // Check if registration is full
        if ($paidEvent->isRegistrationFull()) {
            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('error', 'Registration is full for this event.');
        }

        // Check if user has already registered
        $existingRegistration = Registration::query()
            ->where('paid_event_id', $paidEvent->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingRegistration) {
            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('error', 'You have already registered for this event.');
        }

        $user = auth()->user();

        // Get t-shirt size guideline image
        $tshirtGuidelineUrl = $paidEvent->getFirstMediaUrl('tshirt_size_guideline');

        return Inertia::render('paid-events/register', [
            'paidEvent' => [
                'id' => $paidEvent->id,
                'title' => $paidEvent->title,
                'slug' => $paidEvent->slug,
                'semester' => $paidEvent->semester,
                'registration_fee' => $paidEvent->registration_fee,
                'student_id_rules' => $paidEvent->student_id_rules,
                'student_id_rules_guide' => $paidEvent->student_id_rules_guide,
                'pickup_points' => $paidEvent->pickup_points,
                'departments' => $paidEvent->departments,
                'sections' => $paidEvent->sections,
                'lab_teacher_names' => $paidEvent->lab_teacher_names,
                'tshirt_sizes' => $paidEvent->tshirt_sizes,
                'tshirt_guideline_url' => $tshirtGuidelineUrl,
            ],
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'department' => $user->department,
                'gender' => $user->gender?->value,
            ],
            'tshirtSizes' => $paidEvent->tshirt_sizes
                ? array_map(fn ($size) => ['value' => strtolower($size), 'label' => strtoupper($size)], $paidEvent->tshirt_sizes)
                : [],
            'genders' => array_map(
                fn (Gender $gender) => ['value' => $gender->value, 'label' => $gender->getLabel()],
                Gender::cases()
            ),
        ])->withViewData([
            'SEOData' => new SEOData(
                title: "Register for {$paidEvent->title}",
                description: "Complete your registration for {$paidEvent->title}.",
            ),
        ]);
    }

    public function validateStudentId(Request $request, PaidEvent $paidEvent): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'string'],
        ]);

        $studentId = $request->input('student_id');

        // Check if student ID matches the pattern
        if ($paidEvent->student_id_rules) {
            if (! preg_match($paidEvent->student_id_rules, $studentId)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Your student ID does not meet the eligibility requirements for this event.',
                ], 422);
            }
        }

        // Check if this student ID is already registered for this event
        $existingRegistration = Registration::query()
            ->where('paid_event_id', $paidEvent->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'valid' => false,
                'message' => 'This student ID has already been registered for this event.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Student ID is valid and eligible for registration.',
        ]);
    }

    public function storeRegistration(Request $request, PaidEvent $paidEvent): RedirectResponse
    {
        // Validate the request
        $validated = $request->validate([
            'student_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'department' => ['required', 'string', 'max:255'],
            'section' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'lab_teacher_name' => ['required', 'string', 'max:255'],
            'tshirt_size' => ['required', 'string', 'max:50'],
            'transport_service_required' => ['required', 'boolean'],
            'pickup_point' => ['nullable', 'string', 'max:255', 'required_if:transport_service_required,true'],
        ]);

        // Check if registration is still open
        if (! $paidEvent->isRegistrationOpen()) {
            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('error', 'Registration is no longer open for this event.');
        }

        // Check if registration is full
        if ($paidEvent->isRegistrationFull()) {
            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('error', 'Registration is full for this event.');
        }

        // Validate student ID against rules
        if ($paidEvent->student_id_rules) {
            if (! preg_match($paidEvent->student_id_rules, $validated['student_id'])) {
                return back()->withErrors([
                    'student_id' => 'Your student ID does not meet the eligibility requirements for this event.',
                ])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Create the registration
            Registration::query()->create([
                'paid_event_id' => $paidEvent->id,
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'email' => auth()->user()->email,
                'student_id' => $validated['student_id'],
                'phone' => $validated['phone'],
                'department' => $validated['department'],
                'section' => $validated['section'],
                'gender' => $validated['gender'],
                'lab_teacher_name' => $validated['lab_teacher_name'],
                'tshirt_size' => $validated['tshirt_size'],
                'transport_service_required' => $validated['transport_service_required'],
                'pickup_point' => $validated['pickup_point'],
                'amount' => $paidEvent->registration_fee,
                'status' => RegistrationStatus::PENDING,
            ]);

            DB::commit();

            return redirect()->route('paid-events.show', $paidEvent->slug)
                ->with('success', 'Registration submitted successfully! Please complete the payment to confirm your registration.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'An error occurred while processing your registration. Please try again.')
                ->withInput();
        }
    }
}
