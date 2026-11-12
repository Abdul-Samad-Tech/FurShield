<?php

namespace App\Queries\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;

class UserDirectoryQuery
{
    /**
     * @return array{q: string, role: string, sort: string, direction: string}
     */
    public function filters(Request $request): array
    {
        $sortable = ['name', 'email', 'role', 'created_at'];
        $sort = in_array($request->query('sort'), $sortable, true)
            ? (string) $request->query('sort')
            : 'created_at';
        $direction = $request->query('direction');
        $direction = $direction === 'asc' || $direction === 'desc'
            ? $direction
            : ($sort === 'created_at' ? 'desc' : 'asc');
        $role = UserRole::tryFrom((string) $request->query('role'));

        return [
            'q' => trim((string) $request->query('q', '')),
            'role' => $role?->value ?? '',
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->builder($request)->paginate(10)->withQueryString();
    }

    /**
     * @return LazyCollection<int, array{0: string, 1: string, 2: string, 3: ?string, 4: ?string, 5: ?string}>
     */
    public function export(Request $request): LazyCollection
    {
        return $this->builder($request)
            ->lazy(200)
            ->map(fn (User $user): array => [
                $user->name,
                $user->email,
                $user->role->label(),
                $user->city,
                $user->phone,
                $user->created_at?->format('Y-m-d H:i'),
            ]);
    }

    /**
     * @return Builder<User>
     */
    private function builder(Request $request): Builder
    {
        $filters = $this->filters($request);

        return User::query()
            ->when($filters['role'] !== '', fn (Builder $query): Builder => $query->where('role', $filters['role']))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');
    }
}
