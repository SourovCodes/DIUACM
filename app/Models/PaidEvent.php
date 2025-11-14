<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PaidEvent extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PaidEventFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'semester',
        'description',
        'registration_deadline',
        'registration_start_time',
        'registration_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registration_deadline' => 'datetime',
            'registration_start_time' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include published paid events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to search paid events by title, semester, or slug.
     */
    public function scopeSearch($query, ?string $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', '%'.$searchTerm.'%')
                ->orWhere('semester', 'like', '%'.$searchTerm.'%')
                ->orWhere('slug', 'like', '%'.$searchTerm.'%');
        });
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeOfStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Determine if registration is currently open.
     */
    public function isRegistrationOpen(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->registration_start_time === null || $this->registration_deadline === null) {
            return false;
        }

        return now()->between($this->registration_start_time, $this->registration_deadline, true);
    }

    /**
     * Determine if the registration limit has been reached.
     */
    public function isRegistrationFull(): bool
    {
        if ($this->registration_limit === null) {
            return false;
        }

        // TODO: Implement when registration relationship is added
        // return $this->registrations()->count() >= $this->registration_limit;
        return false;
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('banner_image')
            ->useFallbackUrl(url: asset('images/diuacm.jpeg'))
            ->singleFile()
            ->useDisk(diskName: 'media')
            ->registerMediaConversions(function (?Media $media = null) {
                $this
                    ->addMediaConversion('banner')
                    ->fit(Fit::Contain, 1000, 700)
                    ->nonQueued();
            });
    }
}
