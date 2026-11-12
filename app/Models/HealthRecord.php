<?php

namespace App\Models;

use Database\Factories\HealthRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    /** @use HasFactory<HealthRecordFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pet_id',
        'vet_id',
        'visit_date',
        'next_due_date',
        'recurrence_days',
        'reminder_sent_at',
        'record_type',
        'title',
        'diagnosis',
        'treatment',
        'symptoms',
        'medications',
        'lab_results',
        'allergies',
        'notes',
        'document_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'next_due_date' => 'date',
            'reminder_sent_at' => 'datetime',
            'recurrence_days' => 'integer',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function vet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vet_id');
    }
}
