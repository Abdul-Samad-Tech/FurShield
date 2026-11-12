<?php

namespace App\Models;

use App\Enums\PrivilegeKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class UserPrivilege extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'privilege_key',
        'is_allowed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'privilege_key' => PrivilegeKey::class,
            'is_allowed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Collection<string, bool>
     */
    public static function mapForUser(int $userId): Collection
    {
        $rows = self::query()->where('user_id', $userId)->get();

        return collect(PrivilegeKey::cases())->mapWithKeys(
            function (PrivilegeKey $privilege) use ($rows): array {
                $match = $rows->first(
                    fn (UserPrivilege $item): bool => $item->privilege_key === $privilege
                );

                return [$privilege->value => $match?->is_allowed ?? false];
            }
        );
    }
}
