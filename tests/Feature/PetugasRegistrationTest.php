<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PetugasRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_pending_petugas_account_and_pending_accounts_cannot_sign_in(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Akun dapat digunakan setelah disetujui Admin.');

        $this->post(route('register.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas.baru@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user = User::where('email', 'petugas.baru@example.com')->firstOrFail();

        $this->assertSame('petugas', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertNull($user->approved_at);
        $this->assertTrue(Hash::check('password-123', $user->password));
        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password-123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'Akun Anda belum aktif. Hubungi Admin untuk mendapatkan persetujuan.',
            ]);

        $this->assertGuest();
    }

    public function test_registration_validates_email_uniqueness_and_password_confirmation(): void
    {
        User::factory()->create(['email' => 'registered@example.com']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Petugas Baru',
            'email' => 'registered@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-different',
        ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_can_approve_and_deactivate_petugas_but_petugas_cannot_manage_accounts(): void
    {
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        $petugas->is_active = false;
        $petugas->approved_at = null;
        $petugas->save();

        $this->actingAs($admin)
            ->get(route('admin.petugas.index'))
            ->assertOk()
            ->assertSee($petugas->name)
            ->assertSee('Menunggu')
            ->assertSee('<article', false)
            ->assertSee('Tanggal daftar')
            ->assertSee('Verifikasi / Aktifkan');

        $this->patch(route('admin.petugas.status', $petugas), ['is_active' => 1])
            ->assertRedirect(route('admin.petugas.index'))
            ->assertSessionHas('success');
        $this->assertTrue($petugas->fresh()->is_active);
        $this->assertNotNull($petugas->fresh()->approved_at);

        $this->patch(route('admin.petugas.status', $petugas), ['is_active' => 0])
            ->assertRedirect(route('admin.petugas.index'));
        $this->assertFalse($petugas->fresh()->is_active);

        $this->actingAs($this->createUser('petugas'))
            ->get(route('admin.petugas.index'))
            ->assertForbidden();
    }

    public function test_inactive_petugas_cannot_access_their_dashboard(): void
    {
        $petugas = $this->createUser('petugas');
        $petugas->is_active = false;
        $petugas->save();

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertForbidden();
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
