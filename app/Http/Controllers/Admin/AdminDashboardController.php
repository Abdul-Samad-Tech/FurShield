<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PrivilegeKey;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\RolePrivilege;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Queries\Admin\OrderExportQuery;
use App\Queries\Admin\UserDirectoryQuery;
use App\Services\AdminDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request, AdminDashboardService $dashboard): View
    {
        return view('admin.dashboard', $dashboard->present($request));
    }

    public function exportUsers(Request $request, UserDirectoryQuery $users): StreamedResponse
    {
        return $this->csv(
            'furshield-users-'.now()->toDateString().'.csv',
            ['Name', 'Email', 'Role', 'City', 'Phone', 'Joined'],
            $users->export($request),
        );
    }

    public function exportOrders(OrderExportQuery $orders): StreamedResponse
    {
        return $this->csv(
            'furshield-orders-'.now()->toDateString().'.csv',
            ['Order', 'Owner', 'Email', 'Status', 'Payment', 'Fulfillment', 'Total', 'Order date', 'Items'],
            $orders->rows(),
        );
    }

    public function userPrivileges(Request $request): View
    {
        $users = User::query()
            ->where('role', '!=', UserRole::Admin->value)
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->withCount('privileges')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.user-privileges', [
            'users' => $users,
            'roles' => UserRole::registrable(),
            'privileges' => PrivilegeKey::cases(),
        ]);
    }

    public function editUserPrivileges(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        $roleDefaults = RolePrivilege::query()
            ->where('role', $user->role->value)
            ->where('is_allowed', true)
            ->get()
            ->map(fn (RolePrivilege $item): string => $item->privilege_key->value)
            ->all();

        return view('admin.user-privileges-edit', [
            'user' => $user,
            'privileges' => PrivilegeKey::cases(),
            'userMap' => UserPrivilege::mapForUser($user->id),
            'roleDefaults' => $roleDefaults,
        ]);
    }

    public function updateUserPrivileges(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $validated = $request->validate([
            'privileges' => ['nullable', 'array'],
            'privileges.*' => ['string', Rule::in(PrivilegeKey::values())],
        ]);

        $selected = $validated['privileges'] ?? [];

        foreach (PrivilegeKey::cases() as $privilege) {
            UserPrivilege::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'privilege_key' => $privilege->value,
                ],
                [
                    'is_allowed' => in_array($privilege->value, $selected, true),
                ]
            );
        }

        return redirect()
            ->route('admin.user-privileges')
            ->with('success', 'User privileges updated for '.$user->name.'.');
    }

    public function privileges(): View
    {
        return view('admin.privileges', [
            'roles' => collect(UserRole::cases())->reject(
                fn (UserRole $role): bool => $role === UserRole::Admin
            ),
            'privileges' => PrivilegeKey::cases(),
            'matrix' => RolePrivilege::matrix(),
        ]);
    }

    public function updatePrivileges(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matrix' => ['nullable', 'array'],
        ]);

        $submitted = $validated['matrix'] ?? [];

        foreach (UserRole::cases() as $role) {
            if ($role === UserRole::Admin) {
                continue;
            }

            foreach (PrivilegeKey::cases() as $privilege) {
                $isAllowed = isset($submitted[$role->value][$privilege->value]);

                RolePrivilege::query()->updateOrCreate(
                    [
                        'role' => $role->value,
                        'privilege_key' => $privilege->value,
                    ],
                    [
                        'is_allowed' => $isAllowed,
                    ]
                );
            }
        }

        return back()->with('success', 'Role privileges matrix saved successfully.');
    }

    /**
     * @param  LazyCollection<int, array<int, mixed>>|iterable<int, array<int, mixed>>  $rows
     */
    private function csv(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headings);

            foreach ($rows as $row) {
                fputcsv($out, array_map(fn (mixed $value): string => $this->csvCell($value), $row));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function csvCell(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) === 1 ? "'".$text : $text;
    }
}
