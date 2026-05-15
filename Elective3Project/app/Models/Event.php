<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'venue',
        'max_slots',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getRegisteredCountAttribute(): int
    {
        if (! array_key_exists('registrations_count', $this->attributes)) {
            $this->loadCount('registrations');
        }

        return (int) ($this->attributes['registrations_count'] ?? 0);
    }

    public function getRemainingSlotsAttribute(): int
    {
        return max(0, (int) $this->max_slots - $this->registered_count);
    }

    public function getStatusAttribute(): string
    {
        if ((string) $this->event_date < now()->toDateString()) {
            return 'Concluded';
        }

        if ($this->registered_count >= (int) $this->max_slots) {
            return 'Full';
        }

        return 'Open';
    }

    public function canAcceptRegistration(): bool
    {
        return $this->status === 'Open';
    }

    public function getFormattedStartTimeAttribute(): string
    {
        if (! $this->start_time) {
            return '09:00 AM';
        }

        $time = \DateTime::createFromFormat('H:i:s', $this->start_time);
        return $time ? $time->format('h:i A') : '09:00 AM';
    }

    public function getFormattedEndTimeAttribute(): string
    {
        if (! $this->end_time) {
            return '05:00 PM';
        }

        $time = \DateTime::createFromFormat('H:i:s', $this->end_time);
        return $time ? $time->format('h:i A') : '05:00 PM';
    }

    public function getTimeRangeAttribute(): string
    {
        return "{$this->formatted_start_time} - {$this->formatted_end_time}";
    }
}