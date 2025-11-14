<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    /** @use HasFactory */
    use HasFactory;

    protected $fillable = [
        'paid_event_id',
        'user_id',
        'name',
        'email',
        'student_id',
        'phone',
        'section',
        'department',
        'lab_teacher_name',
        'tshirt_size',
        'gender',
        'transport_service_required',
        'pickup_point',
        'amount',
        'payment_method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'transport_service_required' => 'boolean',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'status' => RegistrationStatus::class,
            'gender' => Gender::class,
        ];
    }

    public function paidEvent(): BelongsTo
    {
        return $this->belongsTo(PaidEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', RegistrationStatus::CONFIRMED);
    }

    /**
     * Scope a query to only include pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', RegistrationStatus::PENDING);
    }

    /**
     * Determine if the registration is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === RegistrationStatus::CONFIRMED;
    }

    /**
     * Determine if the registration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === RegistrationStatus::PENDING;
    }
}
