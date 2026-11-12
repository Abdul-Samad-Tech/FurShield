<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdoptionInterest extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'listing_id',
        'owner_id',
        'message',
        'status',
        'shelter_response',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(AdoptionListing::class, 'listing_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
