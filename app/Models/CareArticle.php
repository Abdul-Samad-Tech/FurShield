<?php

namespace App\Models;

use App\Enums\CareCategory;
use Illuminate\Database\Eloquent\Model;

class CareArticle extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'category',
        'content_type',
        'summary',
        'body',
        'video_url',
        'image_path',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => CareCategory::class,
            'is_published' => 'boolean',
        ];
    }
}
