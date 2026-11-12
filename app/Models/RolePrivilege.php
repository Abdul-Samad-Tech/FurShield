<?php

namespace App\Models;

use App\Enums\PrivilegeKey;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RolePrivilege extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'privilege_key',
        'is_allowed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'privilege_key' => PrivilegeKey::class,
            'is_allowed' => 'boolean',
        ];
    }

    /**
     * @return Collection<string, Collection<string, bool>>
     */
    public static function matrix(): Collection
    {
        $rows = self::query()->get();

        return collect(UserRole::cases())
            ->reject(fn (UserRole $role): bool => $role === UserRole::Admin)
            ->mapWithKeys(function (UserRole $role) use ($rows): array {
                $privileges = collect(PrivilegeKey::cases())->mapWithKeys(
                    function (PrivilegeKey $privilege) use ($rows, $role): array {
                        $match = $rows->first(
                            fn (RolePrivilege $item): bool => $item->role === $role
                                && $item->privilege_key === $privilege
                        );

                        return [$privilege->value => $match?->is_allowed ?? false];
                    }
                );

                return [$role->value => $privileges];
            });
    }
}
