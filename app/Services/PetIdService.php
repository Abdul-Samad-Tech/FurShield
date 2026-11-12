<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\PetSighting;
use App\Support\DummyImages;
use App\Support\PublicPetCard;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PetIdService
{
    /**
     * Fields an owner may place on the public tag. Anything else stays private.
     *
     * @var array<string, string>
     */
    public const FIELDS = [
        'photo' => 'Photo',
        'name' => 'Name',
        'species' => 'Species',
        'breed' => 'Breed',
        'allergies' => 'Allergies',
        'medications' => 'Medications',
        'phone' => 'Call button',
        'whatsapp' => 'WhatsApp button',
    ];

    /**
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        return array_fill_keys(array_keys(self::FIELDS), false);
    }

    public static function token(): string
    {
        do {
            $token = strtolower(Str::random(40));
        } while (Pet::query()->where('public_token', $token)->exists());

        return $token;
    }

    /**
     * @param  list<string>  $enabled
     */
    public function updateSharing(Pet $pet, array $enabled, bool $lost, ?string $note): Pet
    {
        $fields = self::defaults();

        foreach (array_keys($fields) as $key) {
            $fields[$key] = in_array($key, $enabled, true);
        }

        return DB::transaction(function () use ($pet, $fields, $lost, $note) {
            $locked = Pet::query()->whereKey($pet->id)->lockForUpdate()->firstOrFail();
            $locked->public_fields = $fields;
            $locked->is_lost = $lost;
            $locked->lost_note = blank($note) ? null : trim($note);
            $locked->lost_at = $lost ? ($locked->lost_at ?? now()) : null;
            $locked->save();

            return $locked;
        });
    }

    public function regenerate(Pet $pet): Pet
    {
        return DB::transaction(function () use ($pet) {
            $locked = Pet::query()->whereKey($pet->id)->lockForUpdate()->firstOrFail();
            $locked->public_token = self::token();
            $locked->save();

            return $locked;
        });
    }

    public function publicCard(Pet $pet): PublicPetCard
    {
        $pet->loadMissing(['owner', 'healthRecords']);
        $shared = $this->shared($pet);
        $phone = $this->phoneLinks($pet->owner?->phone);

        $photoUrl = null;
        $photoWidth = null;
        $photoHeight = null;
        $photoAlt = null;

        if ($shared['photo'] && filled($pet->photo_path)) {
            $photo = DummyImages::describe($pet->photo_path, 'pet', $pet->id);
            $photoUrl = $photo['url'];
            $photoWidth = $photo['width'];
            $photoHeight = $photo['height'];
            $photoAlt = $shared['name'] ? $pet->name : 'Pet photo';
        }

        return new PublicPetCard(
            lost: (bool) $pet->is_lost,
            lostNote: $pet->is_lost ? $this->text($pet->lost_note) : null,
            name: $shared['name'] ? $this->text($pet->name) : null,
            species: $shared['species'] ? $this->text($pet->species) : null,
            breed: $shared['breed'] ? $this->text($pet->breed) : null,
            photoUrl: $photoUrl,
            photoWidth: $photoWidth,
            photoHeight: $photoHeight,
            photoAlt: $photoAlt,
            allergies: $shared['allergies'] ? $this->alerts($pet, 'allergies') : null,
            medications: $shared['medications'] ? $this->alerts($pet, 'medications') : null,
            callUrl: ($shared['phone'] && $phone) ? $phone['tel'] : null,
            whatsappUrl: ($shared['whatsapp'] && $phone) ? $phone['whatsapp'] : null,
        );
    }

    public function publicUrl(Pet $pet): string
    {
        return route('pets.public', $pet->public_token);
    }

    public function tagSvg(Pet $pet): string
    {
        $url = $this->publicUrl($pet);
        $options = new QROptions([
            'outputBase64' => false,
            'addQuietzone' => true,
            'cssClass' => 'furshield-tag',
            'drawLightModules' => false,
        ]);

        $svg = (new QRCode($options))->render($url);
        $caption = '<title>FurShield pet tag</title><desc>'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'</desc>';

        return (string) preg_replace('/<svg\b([^>]*)>/', '<svg$1>'.$caption, $svg, 1);
    }

    /**
     * @param  array{message: string, finder_name?: string|null, latitude?: float|int|string|null, longitude?: float|int|string|null}  $data
     */
    public function recordSighting(Pet $pet, array $data): PetSighting
    {
        return DB::transaction(function () use ($pet, $data) {
            $locked = Pet::query()->whereKey($pet->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->is_lost, 404);

            $owner = $locked->owner;
            abort_unless($owner, 404);

            $latitude = $this->coordinate($data['latitude'] ?? null);
            $longitude = $this->coordinate($data['longitude'] ?? null);
            $finder = $this->text($data['finder_name'] ?? null);

            $sighting = PetSighting::query()->create([
                'pet_id' => $locked->id,
                'finder_name' => $finder,
                'message' => trim($data['message']),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            $lines = [trim($data['message'])];

            if ($finder) {
                $lines[] = 'From '.$finder;
            }

            if ($latitude !== null && $longitude !== null) {
                $lines[] = 'Location: '.number_format($latitude, 6, '.', '').', '.number_format($longitude, 6, '.', '');
            }

            app(NotificationService::class)->notify(
                $owner,
                $locked->name.' may have been found',
                implode("\n", $lines),
                'alert',
                route('owner.pets.show', $locked),
            );

            return $sighting;
        });
    }

    /**
     * @return array<string, bool>
     */
    private function shared(Pet $pet): array
    {
        $stored = is_array($pet->public_fields) ? $pet->public_fields : [];
        $shared = self::defaults();

        foreach (array_keys($shared) as $key) {
            $shared[$key] = (bool) ($stored[$key] ?? false);
        }

        return $shared;
    }

    private function alerts(Pet $pet, string $column): ?string
    {
        $parts = $pet->healthRecords
            ->pluck($column)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn (string $value) => $value !== '' && strcasecmp($value, 'none') !== 0)
            ->unique()
            ->values();

        return $parts->isEmpty() ? null : $parts->implode(' · ');
    }

    /**
     * @return array{tel: string, whatsapp: string}|null
     */
    private function phoneLinks(?string $phone): ?array
    {
        $trimmed = trim((string) $phone);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return null;
        }

        $tel = str_starts_with($trimmed, '+') ? '+'.$digits : $digits;

        return [
            'tel' => 'tel:'.$tel,
            'whatsapp' => 'https://wa.me/'.$digits,
        ];
    }

    private function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function coordinate(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
