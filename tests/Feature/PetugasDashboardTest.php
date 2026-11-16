<?php

namespace Tests\Feature;

use App\Models\JadwalPakan;
use App\Models\Satwa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetugasDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_only_todays_tasks_and_shows_completion_summary(): void
    {
        $petugas = $this->createUser('petugas');
        $this->actingAs($petugas);
        $satwa = $this->createSatwa();

        JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas A',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Selesai',
            'catatan_petugas' => 'Pakan sudah diberikan.',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas B',
            'tanggal_tugas' => now()->subDay()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('>1</h2>', false)
            ->assertSee('Pakan sudah diberikan.')
            ->assertDontSee('Petugas B');
    }

    public function test_petugas_report_saves_photo_and_updates_task_for_dwh(): void
    {
        Storage::fake('public');

        $petugas = $this->createUser('petugas');
        $petugasLain = $this->createUser('petugas');
        $this->actingAs($petugas);
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas A',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        $satwaLain = Satwa::create([
            'nama_satwa' => 'Satwa Uji Dua',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Uji',
            'status' => 'Dalam pemantauan',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwaLain->id,
            'user_id' => $petugasLain->id,
            'petugas' => 'Petugas B',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->post(route('petugas.laporan', $jadwal->id), [
            'catatan_petugas' => 'Pakan sudah diberikan dengan baik.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])
            ->assertRedirect(route('petugas.dashboard'))
            ->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertSame('Selesai', $jadwal->status);
        $this->assertSame('Pakan sudah diberikan dengan baik.', $jadwal->catatan_petugas);
        $this->assertStringStartsWith('bukti_pakan/', $jadwal->bukti_foto);
        Storage::disk('public')->assertExists($jadwal->bukti_foto);

        $admin = $this->createUser('admin');
        $this->actingAs($admin);
        $this->get(route('dwh.index'))
            ->assertOk()
            ->assertSee('>2</h2>', false)
            ->assertSee('>1</h2>', false)
            ->assertSee('50%')
            ->assertSee('Kandang Uji')
            ->assertSee('1 / 2 tugas selesai')
            ->assertSee('50% kepatuhan kandang');

        $this->get(route('riwayat'))
            ->assertOk()
            ->assertSee('Pakan sudah diberikan dengan baik.')
            ->assertSee('Petugas A')
            ->assertDontSee('Petugas B');
    }

    public function test_report_rejects_non_pending_or_non_today_tasks(): void
    {
        Storage::fake('public');

        $petugas = $this->createUser('petugas');
        $this->actingAs($petugas);
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas A',
            'tanggal_tugas' => now()->subDay()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->post(route('petugas.laporan', $jadwal->id), [
            'catatan_petugas' => 'Laporan terlambat.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertNotFound();

        $this->assertSame('Pending', $jadwal->fresh()->status);
        Storage::disk('public')->assertDirectoryEmpty('bukti_pakan');
    }

    public function test_petugas_history_shows_completed_reports_and_excludes_pending_tasks(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti_pakan/laporan.jpg', 'test-image');

        $petugas = $this->createUser('petugas');
        $this->actingAs($petugas);
        $satwa = $this->createSatwa();
        JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas Riwayat',
            'tanggal_tugas' => now()->subDay()->toDateString(),
            'status' => 'Selesai',
            'catatan_petugas' => 'Laporan historis selesai.',
            'bukti_foto' => 'bukti_pakan/laporan.jpg',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas Riwayat',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
            'catatan_petugas' => 'Catatan tugas pending.',
        ]);

        $this->get(route('petugas.riwayat'))
            ->assertOk()
            ->assertSee('Riwayat Laporan')
            ->assertSee('Kandang Uji')
            ->assertSee('Petugas Riwayat')
            ->assertSee('Laporan historis selesai.')
            ->assertSee('laporan.jpg')
            ->assertDontSee('Catatan tugas pending.');
    }

    public function test_petugas_profile_and_navigation_use_petugas_routes(): void
    {
        $this->actingAs($this->createUser('petugas'));

        $this->get(route('petugas.profil'))
            ->assertOk()
            ->assertSee('Sektor Pemeliharaan Satwa')
            ->assertSee('Siap digunakan')
            ->assertSee(route('petugas.dashboard'), false)
            ->assertSee(route('petugas.dashboard', ['tab' => 'tugas']).'#tugas', false)
            ->assertSee(route('petugas.riwayat'), false)
            ->assertSee(route('petugas.profil'), false)
            ->assertDontSee(route('profil'), false);
    }

    public function test_petugas_can_view_active_satwa_but_not_inactive_satwa(): void
    {
        $this->actingAs($this->createUser('petugas'));
        $activeSatwa = $this->createSatwa();
        $inactiveSatwa = Satwa::create([
            'nama_satwa' => 'Satwa Arsip',
            'kategori' => 'Aves',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Arsip',
            'status' => 'Dalam pemantauan',
            'is_active' => false,
        ]);

        $this->get(route('petugas.satwa'))
            ->assertOk()
            ->assertSee('Satwa Aktif')
            ->assertSee($activeSatwa->nama_satwa)
            ->assertSee($activeSatwa->kandang)
            ->assertDontSee($inactiveSatwa->nama_satwa);
    }

    public function test_admin_satwa_changes_are_reflected_in_current_tasks_and_delete_cascades_reports(): void
    {
        Storage::fake('public');
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        $this->actingAs($admin);

        $this->post(route('satwa.store'), [
            'nama_satwa' => 'Satwa Baru',
            'kategori' => 'Reptil',
            'jumlah_ekor' => 2,
            'kandang' => 'Kandang Baru',
            'status' => 'Dalam pemantauan',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('satwa', ['nama_satwa' => 'Satwa Baru']);

        $satwa = $this->createSatwa();
        Storage::disk('public')->put('bukti_pakan/laporan.jpg', 'test-image');
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => 'Petugas A',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Selesai',
            'catatan_petugas' => 'Laporan untuk satwa ini.',
            'bukti_foto' => 'bukti_pakan/laporan.jpg',
        ]);

        $this->put(route('satwa.update', $satwa), [
            'nama_satwa' => 'Satwa Diperbarui',
            'kategori' => 'Aves',
            'jumlah_ekor' => 3,
            'kandang' => 'Kandang Baru',
            'status' => 'Sehat',
        ])->assertRedirect();

        $this->actingAs($petugas)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Satwa Diperbarui')
            ->assertSee('Kandang Baru')
            ->assertSee('Satwa aktif')
            ->assertSee('>2</h2>', false)
            ->assertSee('3 ekor');

        $this->actingAs($admin)->delete(route('satwa.destroy', $satwa))
            ->assertRedirect();

        $this->assertDatabaseMissing('satwa', ['id' => $satwa->id]);
        $this->assertDatabaseMissing('jadwal_pakan', ['id' => $jadwal->id]);
        Storage::disk('public')->assertMissing('bukti_pakan/laporan.jpg');
        $this->actingAs($petugas)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada penugasan hari ini');
    }

    public function test_randomizer_preserves_completed_tasks_and_schedules_remaining_satwa(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('bukti_pakan/selesai.jpg', 'bukti');

        $admin = $this->createUser('admin');
        $petugasPertama = $this->createUser('petugas');
        $petugasKedua = $this->createUser('petugas');
        $this->actingAs($admin);
        $satwaPertama = $this->createSatwa();
        $satwaKedua = Satwa::create([
            'nama_satwa' => 'Satwa Uji Dua',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Uji Dua',
            'status' => 'Dalam pemantauan',
        ]);

        $jadwalSelesai = JadwalPakan::create([
            'satwa_id' => $satwaPertama->id,
            'user_id' => $petugasPertama->id,
            'petugas' => $petugasPertama->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Selesai',
            'catatan_petugas' => 'Jadwal lama.',
            'bukti_foto' => 'bukti_pakan/selesai.jpg',
        ]);
        $jadwalKemarin = JadwalPakan::create([
            'satwa_id' => $satwaPertama->id,
            'user_id' => $petugasPertama->id,
            'petugas' => 'Petugas Kemarin',
            'tanggal_tugas' => now()->subDay()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->post(route('jadwal.acak'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $jadwalHariIni = JadwalPakan::whereDate('tanggal_tugas', now()->toDateString())->get();
        $this->assertCount(2, $jadwalHariIni);
        $this->assertEqualsCanonicalizing(
            [$satwaPertama->id, $satwaKedua->id],
            $jadwalHariIni->pluck('satwa_id')->all()
        );
        $this->assertSame('Selesai', $jadwalSelesai->fresh()->status);
        $this->assertSame('bukti_pakan/selesai.jpg', $jadwalSelesai->fresh()->bukti_foto);
        $this->assertCount(1, $jadwalHariIni->where('status', 'Pending'));
        $this->assertContains($jadwalHariIni->firstWhere('status', 'Pending')->user_id, [
            $petugasPertama->id,
            $petugasKedua->id,
        ]);
        Storage::disk('public')->assertExists('bukti_pakan/selesai.jpg');
        $this->assertDatabaseHas('jadwal_pakan', ['id' => $jadwalKemarin->id]);
    }

    public function test_randomizer_keeps_existing_tasks_if_no_petugas_account_is_available(): void
    {
        $this->actingAs($this->createUser('admin'));
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'petugas' => 'Petugas Lama',
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->post(route('jadwal.acak'))
            ->assertRedirect()
            ->assertSessionHas('error', 'Belum ada akun Petugas aktif untuk menerima jadwal.');

        $this->assertDatabaseHas('jadwal_pakan', [
            'id' => $jadwal->id,
            'status' => 'Pending',
        ]);
    }

    public function test_randomizer_assigns_only_active_satwa_to_active_petugas(): void
    {
        $this->actingAs($this->createUser('admin'));
        $activePetugas = $this->createUser('petugas');
        $inactivePetugas = $this->createUser('petugas');
        $inactivePetugas->is_active = false;
        $inactivePetugas->save();

        $activeSatwa = $this->createSatwa();
        $inactiveSatwa = Satwa::create([
            'nama_satwa' => 'Satwa Tidak Aktif',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Nonaktif',
            'status' => 'Dalam pemantauan',
            'is_active' => false,
        ]);

        $this->post(route('jadwal.acak'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $jadwalHariIni = JadwalPakan::whereDate('tanggal_tugas', now()->toDateString())->get();
        $this->assertCount(1, $jadwalHariIni);
        $this->assertSame($activeSatwa->id, $jadwalHariIni->first()->satwa_id);
        $this->assertSame($activePetugas->id, $jadwalHariIni->first()->user_id);
        $this->assertDatabaseMissing('jadwal_pakan', [
            'satwa_id' => $inactiveSatwa->id,
            'tanggal_tugas' => now()->toDateString(),
        ]);
    }

    public function test_randomized_assignment_report_updates_petugas_and_admin_views(): void
    {
        Storage::fake('public');

        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        $satwa = $this->createSatwa();

        $this->actingAs($admin)
            ->post(route('jadwal.acak'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $jadwal = JadwalPakan::whereDate('tanggal_tugas', now()->toDateString())->firstOrFail();
        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Penugasan')
            ->assertSee($satwa->nama_satwa)
            ->assertSee('Tandai Selesai');

        $this->post(route('petugas.laporan', $jadwal), [
            'catatan_petugas' => 'Satwa terpantau sehat dan pakan sudah diberikan.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])
            ->assertRedirect(route('petugas.dashboard'))
            ->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertSame('Selesai', $jadwal->status);
        $this->assertSame($petugas->id, $jadwal->user_id);
        Storage::disk('public')->assertExists($jadwal->bukti_foto);

        $this->actingAs($admin)
            ->get(route('dwh.index'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('1 / 1 tugas selesai');

        $this->get(route('jadwal.index'))
            ->assertOk()
            ->assertSee($petugas->name)
            ->assertSee('Satwa terpantau sehat dan pakan sudah diberikan.');
    }

    public function test_petugas_cannot_submit_a_report_for_another_officers_task(): void
    {
        Storage::fake('public');

        $pemilikTugas = $this->createUser('petugas');
        $petugasLain = $this->createUser('petugas');
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $pemilikTugas->id,
            'petugas' => $pemilikTugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->actingAs($petugasLain)->post(route('petugas.laporan', $jadwal), [
            'catatan_petugas' => 'Laporan melalui halaman jadwal Admin.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])
            ->assertNotFound();

        $this->assertSame('Pending', $jadwal->fresh()->status);
        Storage::disk('public')->assertDirectoryEmpty('bukti_pakan');
    }

    private function createSatwa(): Satwa
    {
        return Satwa::create([
            'nama_satwa' => 'Satwa Uji',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Uji',
            'status' => 'Dalam pemantauan',
        ]);
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
