<?php

namespace App\Services;

use App\Enums\HealthRecordType;
use InvalidArgumentException;

class TriageService
{
    /**
     * @param  list<string>  $symptoms
     * @return array{
     *     level: string,
     *     title: string,
     *     reason: string,
     *     tips: list<string>,
     *     rule: string,
     *     species: string,
     *     species_label: string,
     *     symptoms: list<string>,
     *     symptom_labels: list<string>
     * }
     */
    public function assess(string $species, array $symptoms): array
    {
        $speciesKey = $this->speciesKey($species);
        $symptomKeys = $this->symptomKeys($symptoms);
        $levels = config('triage.levels');

        foreach (config('triage.rules') as $rule) {
            if (! $this->matches($rule, $speciesKey, $symptomKeys)) {
                continue;
            }

            $level = $rule['level'];
            $tips = array_values(array_filter([
                $rule['tip'] ?? null,
                ...($levels[$level]['tips'] ?? []),
            ]));

            return [
                'level' => $level,
                'title' => $levels[$level]['title'],
                'reason' => $rule['reason'],
                'tips' => $tips,
                'rule' => $rule['id'],
                'species' => $speciesKey,
                'species_label' => config('triage.species')[$speciesKey],
                'symptoms' => $symptomKeys,
                'symptom_labels' => array_map(
                    fn (string $key): string => config('triage.symptoms')[$key],
                    $symptomKeys
                ),
            ];
        }

        throw new InvalidArgumentException('No triage rule matched.');
    }

    public function disclaimer(): string
    {
        return (string) config('triage.disclaimer');
    }

    /**
     * @param  array{title: string, reason: string, symptom_labels: list<string>}  $assessment
     * @return array{title: string, record_type: string, symptoms: string, notes: string}
     */
    public function timeline(array $assessment): array
    {
        return [
            'title' => 'Symptom check',
            'record_type' => HealthRecordType::Illness->value,
            'symptoms' => implode(', ', $assessment['symptom_labels']),
            'notes' => $assessment['title'].'. '.$assessment['reason'].' '.$this->disclaimer(),
        ];
    }

    /**
     * @param  array<string, mixed>  $rule
     * @param  list<string>  $symptoms
     */
    private function matches(array $rule, string $species, array $symptoms): bool
    {
        $allowed = $rule['species'] ?? null;

        if (is_array($allowed) && ! in_array($species, $allowed, true)) {
            return false;
        }

        $needed = $rule['symptoms'] ?? [];

        if ($needed === []) {
            return true;
        }

        $selected = array_flip($symptoms);
        $hits = 0;

        foreach ($needed as $symptom) {
            if (isset($selected[$symptom])) {
                $hits++;
            }
        }

        return ($rule['match'] ?? 'any') === 'all'
            ? $hits === count($needed)
            : $hits > 0;
    }

    private function speciesKey(string $species): string
    {
        if (! array_key_exists($species, config('triage.species'))) {
            throw new InvalidArgumentException('Choose a species from the list.');
        }

        return $species;
    }

    /**
     * @param  list<string>  $symptoms
     * @return list<string>
     */
    private function symptomKeys(array $symptoms): array
    {
        $known = config('triage.symptoms');
        $keys = [];

        foreach ($symptoms as $symptom) {
            if (! is_string($symptom) || ! array_key_exists($symptom, $known)) {
                throw new InvalidArgumentException('Choose a symptom from the list.');
            }

            $keys[$symptom] = $symptom;
        }

        if ($keys === []) {
            throw new InvalidArgumentException('Choose at least one symptom.');
        }

        return array_values($keys);
    }
}
