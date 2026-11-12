<?php

namespace App\Models;

use App\Enums\CareStatus;
use App\Services\PetIdService;
use App\Services\ReminderService;
use Database\Factories\PetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    /** @use HasFactory<PetFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'medical_history',
        'photo_path',
        'gallery',
        'public_token',
        'public_fields',
        'is_lost',
        'lost_note',
        'lost_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'public_fields' => 'array',
            'is_lost' => 'boolean',
            'lost_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pet $pet): void {
            if (blank($pet->public_token)) {
                $pet->public_token = PetIdService::token();
            }

            if (! is_array($pet->public_fields)) {
                $pet->public_fields = PetIdService::defaults();
            }
        });
    }

    public function shares(string $field): bool
    {
        return (bool) ($this->public_fields[$field] ?? false);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class)->orderByDesc('visit_date');
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function sightings(): HasMany
    {
        return $this->hasMany(PetSighting::class)->latest();
    }

    public function careStatus(): CareStatus
    {
        return app(ReminderService::class)->statusFor($this);
    }
}
