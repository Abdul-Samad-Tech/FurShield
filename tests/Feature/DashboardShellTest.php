<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_uses_the_shared_shell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('owner.dashboard'));

        $response->assertOk();
        $response->assertSee('Skip to content', false);
        $response->assertSee('My Pets', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('aria-label="Search"', false);
        $response->assertDontSee('>Menu</button>', false);
    }

    public function test_each_role_gets_its_own_navigation(): void
    {
        $expectations = [
            [UserRole::Vet, 'vet.dashboard', 'My Profile'],
            [UserRole::Shelter, 'shelter.dashboard', 'Listings'],
            [UserRole::Admin, 'admin.dashboard', 'Role Matrix'],
        ];

        foreach ($expectations as [$role, $route, $label]) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee($label, false)
                ->assertSee('aria-current="page"', false);
        }
    }

    public function test_public_pages_share_the_same_shell(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Skip to content', false);
        $response->assertSee('aria-label="Primary"', false);
    }

    public function test_the_style_guide_is_not_registered_outside_local(): void
    {
        $this->assertFalse(app()->environment('local'));

        $this->get('/dev/ui')->assertNotFound();
    }
}
