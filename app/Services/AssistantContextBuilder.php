<?php

namespace App\Services;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Collection;

class AssistantContextBuilder
{
    public function __construct(private readonly AssistantGuard $guard) {}

    /**
     * @return array{prompt: string, species: list<string>}
     */
    public function build(User $owner): array
    {
        $pets = $owner->pets()->orderBy('name')->get();

        if ($pets->isEmpty()) {
            return [
                'prompt' => 'This owner has no pet profiles yet.',
                'species' => [],
            ];
        }

        $petIds = $pets->pluck('id');
        $allergies = $this->allergies($petIds);
        $records = HealthRecord::query()
            ->whereIn('pet_id', $petIds)
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $names = $pets->pluck('name', 'id');
        $lines = ['Pet profiles for this owner. Use them only to answer. Do not invent pets.'];

        foreach ($pets as $pet) {
            $lines[] = $this->profileLine($pet, $allergies->get($pet->id, ''));
        }

        $lines[] = 'Recent records, newest first. These are existing notes, not a new diagnosis.';

        if ($records->isEmpty()) {
            $lines[] = 'No health records yet.';
        }

        foreach ($records as $record) {
            $lines[] = $this->recordLine($record, (string) ($names[$record->pet_id] ?? 'Pet'));
        }

        return [
            'prompt' => implode("\n", $lines),
            'species' => $pets->pluck('species')->filter()->map(fn ($species): string => (string) $species)->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, int>  $petIds
     * @return Collection<int, string>
     */
    private function allergies(Collection $petIds): Collection
    {
        return HealthRecord::query()
            ->whereIn('pet_id', $petIds)
            ->whereNotNull('allergies')
            ->where('allergies', '!=', '')
            ->orderByDesc('visit_date')
            ->get(['pet_id', 'allergies'])
            ->groupBy('pet_id')
            ->map(function (Collection $rows): string {
                return $rows
                    ->pluck('allergies')
                    ->map(fn ($value): string => $this->plain((string) $value, 80))
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->implode('; ');
            });
    }

    private function profileLine(Pet $pet, string $allergies): string
    {
        $age = $pet->age === null ? 'unknown' : ((int) $pet->age).' years';
        $allergy = $allergies !== '' ? $allergies : 'none recorded';
        $line = sprintf(
            '- %s. Species: %s. Age: %s. Allergies: %s.',
            $this->plain((string) $pet->name, 40),
            $this->plain((string) $pet->species, 40),
            $age,
            $allergy
        );

        $history = $this->plain((string) $pet->medical_history, 180);

        if ($history !== '' && str_contains(mb_strtolower($history), 'allerg')) {
            $line .= ' Profile note: '.$this->guard->redactDoses($history).'.';
        }

        return $line;
    }

    private function recordLine(HealthRecord $record, string $petName): string
    {
        $symptoms = $this->plain((string) $record->symptoms, 160);
        $symptoms = $symptoms === '' ? 'none recorded' : $this->guard->redactDoses($symptoms);
        $date = $record->visit_date?->toDateString() ?? 'unknown date';

        return sprintf(
            '- %s. Date: %s. Type: %s. Title: %s. Symptoms: %s.',
            $this->plain($petName, 40),
            $date,
            $this->plain((string) $record->record_type, 40),
            $this->plain((string) $record->title, 80),
            $symptoms
        );
    }

    private function plain(string $value, int $limit): string
    {
        $collapsed = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return mb_substr($collapsed, 0, $limit);
    }
}
