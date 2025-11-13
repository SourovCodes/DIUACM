<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidEvent extends Model
{
    /** @use HasFactory<\Database\Factories\PaidEventFactory> */
    use HasFactory;

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
}
