<?php

namespace App\Support;

/**
 * The only pet data a public tag page is allowed to receive.
 */
final class PublicPetCard
{
    public function __construct(
        public bool $lost,
        public ?string $lostNote,
        public ?string $name,
        public ?string $species,
        public ?string $breed,
        public ?string $photoUrl,
        public ?int $photoWidth,
        public ?int $photoHeight,
        public ?string $photoAlt,
        public ?string $allergies,
        public ?string $medications,
        public ?string $callUrl,
        public ?string $whatsappUrl,
    ) {}

    public function hasDetails(): bool
    {
        return $this->name
            || $this->species
            || $this->breed
            || $this->photoUrl
            || $this->allergies
            || $this->medications
            || $this->callUrl
            || $this->whatsappUrl;
    }
}
