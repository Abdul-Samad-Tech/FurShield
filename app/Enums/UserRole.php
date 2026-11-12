<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Vet = 'vet';
    case Shelter = 'shelter';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pet Owner',
            self::Vet => 'Veterinarian',
            self::Shelter => 'Animal Shelter',
            self::Admin => 'Administrator',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Owner => 'owner.dashboard',
            self::Vet => 'vet.dashboard',
            self::Shelter => 'shelter.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function registrable(): array
    {
        return [self::Owner, self::Vet, self::Shelter];
    }
}
