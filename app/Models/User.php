<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'shelter_name',
        'contact_person',
        'specialization',
        'experience_years',
        'bio',
        'available_slots',
        'city',
        'latitude',
        'longitude',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'available_slots' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function isVet(): bool
    {
        return $this->role === UserRole::Vet;
    }

    public function isShelter(): bool
    {
        return $this->role === UserRole::Shelter;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    public function vetAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'vet_id');
    }

    public function ownerAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'owner_id');
    }

    public function adoptionListings(): HasMany
    {
        return $this->hasMany(AdoptionListing::class, 'shelter_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'owner_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function privileges(): HasMany
    {
        return $this->hasMany(UserPrivilege::class);
    }
}
