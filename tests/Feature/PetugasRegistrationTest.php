<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    public function test_admin_can_save_and_normalize_a_petugas_whatsapp_number(): void
    {
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');

        $this->actingAs($admin)
            ->patch(route('admin.petugas.phone', $petugas), [
                'nomor_hp' => '0812-3456-7890',
            ])
            ->assertRedirect(route('admin.petugas.index'))
            ->assertSessionHas('success');

        $this->assertSame('6281234567890', $petugas->fresh()->nomor_hp);

        $this->from(route('admin.petugas.index'))
            ->patch(route('admin.petugas.phone', $petugas), [
                'nomor_hp' => 'nomor-tidak-valid',
                'phone_user_id' => $petugas->id,
            ])
            ->assertRedirect(route('admin.petugas.index'))
            ->assertSessionHasErrors('nomor_hp');
        $this->assertSame('6281234567890', $petugas->fresh()->nomor_hp);
    }

    public function test_phone_update_shows_migration_guidance_when_the_column_is_missing(): void
    {
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nomor_hp');
        });

        $this->actingAs($admin)
            ->from(route('admin.petugas.index'))
            ->patch(route('admin.petugas.phone', $petugas), [
                'nomor_hp' => '0812-3456-7890',
            ])
            ->assertRedirect(route('admin.petugas.index'))
            ->assertSessionHasErrors([
                'nomor_hp' => 'Kolom nomor HP belum tersedia. Jalankan php artisan migrate lalu coba lagi.',
            ]);
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
