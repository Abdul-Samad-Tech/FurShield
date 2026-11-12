<?php

namespace App\Models;

use App\Enums\AdoptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdoptionListing extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'shelter_id',
        'pet_name',
        'species',
        'breed',
        'age',
        'gender',
        'health_status',
        'description',
        'photo_path',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AdoptionStatus::class,
        ];
    }

    public function shelter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shelter_id');
    }

    public function careLogs(): HasMany
    {
        return $this->hasMany(ShelterCareLog::class, 'listing_id')->orderByDesc('logged_at');
    }

    public function interests(): HasMany
    {
        return $this->hasMany(AdoptionInterest::class, 'listing_id');
    }
}
