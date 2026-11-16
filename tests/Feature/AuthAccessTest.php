<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_sign_in_to_enter_either_module(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
        $this->get(route('petugas.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_and_petugas_routes_are_role_restricted(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
        $this->get(route('petugas.dashboard'))->assertForbidden();

        $petugas = $this->createUser('petugas');
        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk();
        $this->get(route('satwa.index'))->assertForbidden();
    }

    public function test_login_and_logout_use_session_authentication(): void
    {
        $petugas = $this->createUser('petugas');

        $this->post(route('login.store'), [
            'email' => $petugas->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('petugas.dashboard'));

        $this->assertAuthenticatedAs($petugas);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_petugas_visiting_login_is_returned_to_their_module(): void
    {
        $petugas = $this->createUser('petugas');

        $this->actingAs($petugas)
            ->get(route('login'))
            ->assertRedirect(route('petugas.dashboard'));
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
