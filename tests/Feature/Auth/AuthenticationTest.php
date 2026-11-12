<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in as a role');
        $response->assertSee('Pet Owner');
        $response->assertSee('Veterinarian');
        $response->assertSee('Animal Shelter');
        $response->assertSee('Administrator');
        $response->assertSee('owner@furshield.test');
        $response->assertSee('vet@furshield.test');
        $response->assertSee('shelter@furshield.test');
        $response->assertSee('admin@furshield.test');
    }

    public function test_a_demo_role_button_signs_in_as_that_account(): void
    {
        User::factory()->owner()->create(['email' => 'owner@furshield.test']);

        $this->post('/login', [
            'email' => 'owner@furshield.test',
            'password' => 'password',
        ])->assertRedirect(route('owner.dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertSame('owner@furshield.test', auth()->user()->email);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('owner.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
