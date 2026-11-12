<?php

namespace App\Services;

use App\Models\AdoptionListing;
use App\Support\DummyImages;
use Illuminate\Http\Request;

class AdoptionMatchService
{
    /**
     * @return array{home: string, kids: string, pets: string, experience: string, activity: string}|null
     */
    public function answers(Request $request): ?array
    {
        $answers = [
            'home' => $request->string('home')->toString(),
            'kids' => $request->string('kids')->toString(),
            'pets' => $request->string('pets')->toString(),
            'experience' => $request->string('experience')->toString(),
            'activity' => $request->string('activity')->toString(),
        ];

        $allowed = [
            'home' => ['apartment', 'house', 'either'],
            'kids' => ['yes', 'no', 'either'],
            'pets' => ['yes', 'no', 'either'],
            'experience' => ['first', 'experienced', 'either'],
            'activity' => ['quiet', 'moderate', 'high'],
        ];

        foreach ($allowed as $key => $values) {
            if (! in_array($answers[$key], $values, true)) {
                return null;
            }
        }

        return $answers;
    }

    /**
     * @param  array{home: string, kids: string, pets: string, experience: string, activity: string}  $answers
     */
    public function score(AdoptionListing $listing, array $answers): int
    {
        $text = strtolower(implode(' ', array_filter([
            $listing->species,
            $listing->breed,
            $listing->description,
            $listing->health_status,
        ])));

        return $this->home($text, $answers['home'])
            + $this->kids($text, $answers['kids'])
            + $this->pets($text, $answers['pets'])
            + $this->experience($text, $listing, $answers['experience'])
            + $this->activity($text, $listing, $answers['activity']);
    }

    /**
     * @return array<string, mixed>
     */
    public function card(AdoptionListing $listing, ?int $match): array
    {
        $image = DummyImages::describe($listing->photo_path, 'adoption', $listing->id);
        $shelter = $listing->shelter;

        return [
            'id' => $listing->id,
            'name' => $listing->pet_name,
            'species' => $listing->species,
            'breed' => $listing->breed,
            'age' => $listing->age,
            'gender' => $listing->gender,
            'health' => $listing->health_status,
            'description' => (string) $listing->description,
            'status' => $listing->status?->label() ?? 'Available',
            'shelter' => $shelter->shelter_name ?? $shelter->name,
            'city' => $shelter->city,
            'image' => $image['url'],
            'url' => route('adopt.show', $listing),
            'interest_url' => route('adopt.interest', $listing),
            'match' => $match,
        ];
    }

    private function home(string $text, string $answer): int
    {
        if ($answer === 'either') {
            return 16;
        }

        $apartment = $this->has($text, ['cat', 'kitten', 'lap', 'calm', 'indoor', 'windowsill', 'quiet']);
        $house = $this->has($text, ['dog', 'puppy', 'active', 'playful', 'yard']);

        if ($answer === 'apartment') {
            if ($apartment && ! $house) {
                return 20;
            }

            if ($house && ! $apartment) {
                return 4;
            }

            return $apartment ? 12 : 10;
        }

        if ($house && ! $apartment) {
            return 20;
        }

        if ($apartment && ! $house) {
            return 4;
        }

        return $house ? 12 : 10;
    }

    private function kids(string $text, string $answer): int
    {
        if ($answer === 'either') {
            return 16;
        }

        $friendly = $this->has($text, ['kid', 'child', 'family', 'friendly']);
        $quiet = $this->has($text, ['calm', 'lap', 'quiet', 'independent']);

        if ($answer === 'yes') {
            if ($friendly) {
                return 20;
            }

            return $quiet ? 8 : 10;
        }

        if ($quiet) {
            return 18;
        }

        return $friendly ? 12 : 14;
    }

    private function pets(string $text, string $answer): int
    {
        if ($answer === 'either') {
            return 16;
        }

        $social = $this->has($text, ['friendly', 'playful', 'social']);
        $solo = $this->has($text, ['only pet', 'no other', 'territorial', 'anxious']);
        $calm = $this->has($text, ['calm', 'lap', 'independent']);

        if ($answer === 'yes') {
            if ($solo) {
                return 4;
            }

            return $social ? 20 : 12;
        }

        if ($solo || $calm) {
            return 18;
        }

        return $social ? 12 : 14;
    }

    private function experience(string $text, AdoptionListing $listing, string $answer): int
    {
        if ($answer === 'either') {
            return 16;
        }

        $young = $listing->age !== null && $listing->age <= 1;
        $calm = $this->has($text, ['calm', 'lap', 'quiet']);
        $busy = $young || $this->has($text, ['playful', 'active', 'puppy', 'kitten']);

        if ($answer === 'first') {
            if ($calm && ! $busy) {
                return 20;
            }

            return $busy ? 6 : 12;
        }

        if ($busy) {
            return 20;
        }

        return $calm ? 12 : 14;
    }

    private function activity(string $text, AdoptionListing $listing, string $answer): int
    {
        if ($answer === 'moderate') {
            if ($listing->age !== null && $listing->age >= 2 && $listing->age <= 6) {
                return 16;
            }

            return 12;
        }

        $calm = $this->has($text, ['calm', 'lap', 'quiet', 'windowsill']);
        $active = $this->has($text, ['playful', 'active', 'run', 'fetch', 'yard']);
        $young = $listing->age !== null && $listing->age <= 1;

        if ($answer === 'quiet') {
            if ($calm && ! $active) {
                return 20;
            }

            return ($active || $young) ? 4 : 10;
        }

        if ($active || $young) {
            return 20;
        }

        return $calm ? 6 : 10;
    }

    /**
     * @param  list<string>  $needles
     */
    private function has(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }
}
