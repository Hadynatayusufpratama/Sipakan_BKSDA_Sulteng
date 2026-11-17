<?php

namespace Tests\Feature;

use App\Models\JadwalPakan;
use App\Models\Satwa;
use App\Models\User;
use App\Services\SatwaCheckpointQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ->assertSee('Semua tugas hari ini sudah selesai.')
            ->assertDontSee('Petugas B');
    }

    public function test_dashboard_daily_notice_only_lists_the_authenticated_officers_pending_tasks(): void
    {
        $petugas = $this->createUser('petugas');
        $petugasLain = $this->createUser('petugas');
        $satwaPetugas = $this->createSatwa();
        $satwaPetugasLain = Satwa::create([
            'nama_satwa' => 'Satwa Petugas Lain',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Lain',
            'status' => 'Dalam pemantauan',
        ]);
        $jadwalPetugas = JadwalPakan::create([
            'satwa_id' => $satwaPetugas->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwaPetugasLain->id,
            'user_id' => $petugasLain->id,
            'petugas' => $petugasLain->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee("Halo {$petugas->name}")
            ->assertSee($satwaPetugas->nama_satwa)
            ->assertSee('href="#task-' . $jadwalPetugas->id . '"', false)
            ->assertDontSee($satwaPetugasLain->nama_satwa);
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

        $this->scanCheckpoint($jadwal, $satwa);

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
        $dwhResponse = $this->get(route('dwh.index'))
            ->assertOk()
            ->assertSee('>2</h2>', false)
            ->assertSee('>1</h2>', false)
            ->assertSee('50%')
            ->assertSee('Kandang Uji')
            ->assertSee('1 / 2 tugas selesai')
            ->assertSee('50% kepatuhan kandang');
        $dwhResponse
            ->assertViewHas('jadwalPending', 1)
            ->assertViewHas('activePetugas', 2)
            ->assertViewHas('dailyTrend', function ($dailyTrend) {
                $today = $dailyTrend->firstWhere('date', now()->toDateString());

                return $dailyTrend->count() === 7
                    && $today?->total_tasks === 2
                    && $today?->completed_tasks === 1;
            })
            ->assertViewHas('weeklyTrend', function ($weeklyTrend) {
                return $weeklyTrend->count() === 8
                    && $weeklyTrend->sum('total_tasks') === 2
                    && $weeklyTrend->sum('completed_tasks') === 1;
            });

        $this->get(route('riwayat'))
            ->assertOk()
            ->assertSee('Pakan sudah diberikan dengan baik.')
            ->assertSee('Petugas A')
            ->assertSee(asset('storage/' . $jadwal->bukti_foto))
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

    public function test_checkpoint_requires_a_valid_qr_and_the_assigned_todays_task(): void
    {
        $petugas = $this->createUser('petugas');
        $petugasLain = $this->createUser('petugas');
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        $qrPayload = app(SatwaCheckpointQr::class)->payloadFor($satwa);
        $forgedPayload = json_decode($qrPayload, true);
        $forgedPayload['signature'] = str_repeat('0', 64);

        $this->actingAs($petugas)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwal->id,
                'qr_payload' => json_encode($forgedPayload),
            ])
            ->assertUnprocessable();
        $this->assertEmpty(session("petugas_checkpoint_scans_{$petugas->id}", []));

        $this->actingAs($petugasLain)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwal->id,
                'qr_payload' => $qrPayload,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Jadwal pakan untuk kandang ini belum didistribusikan hari ini.')
            ->assertJsonPath('errors.task_id.0', 'Jadwal pakan untuk kandang ini belum didistribusikan hari ini.');
        $this->assertEmpty(session("petugas_checkpoint_scans_{$petugasLain->id}", []));

        $this->actingAs($petugas)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwal->id,
                'qr_payload' => $qrPayload,
            ])
            ->assertOk()
            ->assertJsonPath('task_id', $jadwal->id)
            ->assertJsonPath('scanned_at', session("petugas_checkpoint_scans_{$petugas->id}.{$jadwal->id}"));

        $scannedAt = session("petugas_checkpoint_scans_{$petugas->id}.{$jadwal->id}");
        $this->assertNotEmpty($scannedAt);

        $this->postJson(route('petugas.checkpoint'), [
            'task_id' => $jadwal->id,
            'qr_payload' => $qrPayload,
        ])->assertOk()->assertJsonPath('scanned_at', $scannedAt);

        $this->assertSame($scannedAt, session("petugas_checkpoint_scans_{$petugas->id}.{$jadwal->id}"));
    }

    public function test_checkpoint_uses_the_local_calendar_date_for_today_tasks(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16 17:00:00', 'UTC'));
        $this->assertSame('2026-11-17', now()->toDateString());

        $petugas = $this->createUser('petugas');
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        DB::table('jadwal_pakan')
            ->where('id', $jadwal->id)
            ->update(['updated_at' => now()->subMinute()]);
        $qrPayload = app(SatwaCheckpointQr::class)->payloadFor($satwa);

        $this->actingAs($petugas)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwal->id,
                'qr_payload' => $qrPayload,
            ])
            ->assertOk()
            ->assertJsonPath('task_id', $jadwal->id);

        $this->assertTrue($jadwal->fresh()->updated_at->equalTo(now()));
        $this->assertNotEmpty(session("petugas_checkpoint_scans_{$petugas->id}.{$jadwal->id}"));
    }

    public function test_checkpoint_and_report_work_without_a_checkpoint_column(): void
    {
        Schema::table('jadwal_pakan', function (Blueprint $table) {
            $table->dropColumn('checkpoint_at');
        });
        $this->assertFalse(Schema::hasColumn('jadwal_pakan', 'checkpoint_at'));

        Storage::fake('public');
        $petugas = $this->createUser('petugas');
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        $qrPayload = app(SatwaCheckpointQr::class)->payloadFor($satwa);

        $this->actingAs($petugas)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwal->id,
                'qr_payload' => $qrPayload,
            ])
            ->assertOk()
            ->assertJsonPath('task_id', $jadwal->id);

        $this->assertNotNull($jadwal->fresh()->updated_at);

        $this->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Checkpoint tercatat')
            ->assertSee('Kirim Laporan & Tandai Selesai', false);

        $this->post(route('petugas.laporan', $jadwal), [
            'catatan_petugas' => 'Laporan tanpa kolom checkpoint khusus.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect(route('petugas.dashboard'));

        $this->assertSame('Selesai', $jadwal->fresh()->status);
    }

    public function test_petugas_cannot_submit_a_report_before_scanning_checkpoint(): void
    {
        Storage::fake('public');

        $petugas = $this->createUser('petugas');
        $satwa = $this->createSatwa();
        $jadwal = JadwalPakan::create([
            'satwa_id' => $satwa->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);

        $this->actingAs($petugas)->post(route('petugas.laporan', $jadwal), [
            'catatan_petugas' => 'Pakan sudah diberikan.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertNotFound();

        $this->assertSame('Pending', $jadwal->fresh()->status);
        $this->assertEmpty(session("petugas_checkpoint_scans_{$petugas->id}", []));
        Storage::disk('public')->assertDirectoryEmpty('bukti_pakan');
    }

    public function test_checkpoint_falls_back_to_latest_pending_assignment_and_unlocks_its_report(): void
    {
        Storage::fake('public');

        $petugas = $this->createUser('petugas');
        $satwaHariIni = $this->createSatwa();
        $satwaTertunda = Satwa::create([
            'nama_satwa' => 'Satwa Tugas Tertunda',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Tertunda',
            'status' => 'Dalam pemantauan',
        ]);
        $jadwalHariIni = JadwalPakan::create([
            'satwa_id' => $satwaHariIni->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        $jadwalTertunda = JadwalPakan::create([
            'satwa_id' => $satwaTertunda->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->subDay()->toDateString(),
            'status' => 'Pending',
        ]);
        $qrPayload = app(SatwaCheckpointQr::class)->payloadFor($satwaTertunda);

        $this->actingAs($petugas)
            ->postJson(route('petugas.checkpoint'), [
                'task_id' => $jadwalHariIni->id,
                'qr_payload' => $qrPayload,
            ])
            ->assertOk()
            ->assertJsonPath('task_id', $jadwalTertunda->id);

        $this->assertNotEmpty(session("petugas_checkpoint_scans_{$petugas->id}.{$jadwalTertunda->id}"));

        $this->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee($satwaTertunda->nama_satwa)
            ->assertSee('Checkpoint tercatat')
            ->assertSee('Kirim Laporan & Tandai Selesai', false);

        $this->post(route('petugas.laporan', $jadwalTertunda), [
            'catatan_petugas' => 'Pakan diberikan setelah checkpoint.',
            'bukti_foto' => UploadedFile::fake()->image('bukti.jpg'),
        ])
            ->assertRedirect(route('petugas.dashboard'))
            ->assertSessionHas('success');

        $this->assertSame('Selesai', $jadwalTertunda->fresh()->status);
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
            ->assertSee(asset('storage/bukti_pakan/laporan.jpg'))
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
            ->assertSessionHas('error', 'Belum ada akun Petugas aktif dan terverifikasi untuk menerima jadwal.');

        $this->assertDatabaseHas('jadwal_pakan', [
            'id' => $jadwal->id,
            'status' => 'Pending',
        ]);
    }

    public function test_randomizer_assigns_active_satwa_fairly_only_to_approved_active_petugas(): void
    {
        $this->actingAs($this->createUser('admin'));
        $approvedPetugas = [
            $this->createUser('petugas'),
            $this->createUser('petugas'),
        ];
        $unapprovedPetugas = $this->createUser('petugas');
        $unapprovedPetugas->forceFill(['approved_at' => null])->save();

        foreach (range(1, 5) as $number) {
            Satwa::create([
                'nama_satwa' => "Satwa Uji {$number}",
                'kategori' => 'Mamalia',
                'jumlah_ekor' => 1,
                'kandang' => 'Kandang Uji',
                'status' => 'Dalam pemantauan',
            ]);
        }

        $this->post(route('jadwal.acak'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $jadwalHariIni = JadwalPakan::whereDate('tanggal_tugas', now()->toDateString())->get();
        $this->assertCount(5, $jadwalHariIni);
        $this->assertEmpty($jadwalHariIni->where('user_id', $unapprovedPetugas->id));
        $this->assertEqualsCanonicalizing(
            collect($approvedPetugas)->pluck('id')->all(),
            $jadwalHariIni->pluck('user_id')->unique()->all()
        );

        $jumlahPerPetugas = $jadwalHariIni->countBy('user_id');
        $this->assertLessThanOrEqual(
            1,
            abs($jumlahPerPetugas->get($approvedPetugas[0]->id) - $jumlahPerPetugas->get($approvedPetugas[1]->id))
        );
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
        $this->actingAs($admin)
            ->get(route('satwa.index'))
            ->assertOk()
            ->assertSee("qr-code-{$satwa->id}")
            ->assertSee('Cetak QR');

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Penugasan')
            ->assertSee($satwa->nama_satwa)
            ->assertSee('Scan QR Kandang')
            ->assertDontSee('Kirim Laporan & Tandai Selesai');

        $this->scanCheckpoint($jadwal, $satwa);
        $this->assertNotEmpty(session("petugas_checkpoint_scans_{$petugas->id}.{$jadwal->id}"));

        $this->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Checkpoint tercatat')
            ->assertSee('Kirim Laporan & Tandai Selesai', false);

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

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee(asset('storage/' . $jadwal->bukti_foto));

        $this->actingAs($admin)
            ->get(route('dwh.index'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('1 / 1 tugas selesai');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('jadwalHariIni', function ($jadwalHariIni) {
                return $jadwalHariIni->where('status', 'Selesai')->count() === 1;
            });

        $this->get(route('jadwal.index'))
            ->assertOk()
            ->assertSee($petugas->name)
            ->assertSee(asset('storage/' . $jadwal->bukti_foto))
            ->assertSee($jadwal->updated_at->format('Y-m-d H:i:s'))
            ->assertSee('Satwa terpantau sehat dan pakan sudah diberikan.');
    }

    public function test_admin_jadwal_filters_by_month_and_renders_printable_report_summary(): void
    {
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        $satwaSelesai = $this->createSatwa();
        $satwaPending = Satwa::create([
            'nama_satwa' => 'Satwa Bulan Berjalan',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Bulanan',
            'status' => 'Dalam pemantauan',
        ]);
        $satwaLama = Satwa::create([
            'nama_satwa' => 'Satwa Bulan Sebelumnya',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Lama',
            'status' => 'Dalam pemantauan',
        ]);
        $bulanIni = now()->startOfMonth()->toDateString();

        JadwalPakan::create([
            'satwa_id' => $satwaSelesai->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => $bulanIni,
            'status' => 'Selesai',
            'catatan_petugas' => 'Pakan diberikan sesuai jadwal.',
            'bukti_foto' => 'bukti_pakan/laporan-bulanan.jpg',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwaPending->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->toDateString(),
            'status' => 'Pending',
        ]);
        JadwalPakan::create([
            'satwa_id' => $satwaLama->id,
            'user_id' => $petugas->id,
            'petugas' => $petugas->name,
            'tanggal_tugas' => now()->subMonth()->startOfMonth()->toDateString(),
            'status' => 'Selesai',
        ]);

        $this->actingAs($admin)
            ->get(route('jadwal.index', ['filter_bulan' => now()->format('Y-m')]))
            ->assertOk()
            ->assertViewHas('jadwal', fn ($jadwal) => $jadwal->count() === 2)
            ->assertViewHas('totalTugas', 2)
            ->assertViewHas('totalSelesai', 1)
            ->assertViewHas('totalPending', 1)
            ->assertViewHas('persentaseSelesai', 50)
            ->assertSee('BKSDA Sulteng')
            ->assertSee('Cetak Laporan Bulanan')
            ->assertSee('Sudah Diberi Makan')
            ->assertSee('Belum Diberi Makan')
            ->assertSee(asset('storage/bukti_pakan/laporan-bulanan.jpg'))
            ->assertDontSee('Satwa Bulan Sebelumnya');
    }

    public function test_randomizer_generates_admin_and_per_task_whatsapp_reminder_links(): void
    {
        $admin = $this->createUser('admin');
        $petugas = $this->createUser('petugas');
        $petugas->forceFill(['name' => 'Fadil'])->save();
        $satwa = Satwa::create([
            'nama_satwa' => 'Harimau Malaya',
            'kategori' => 'Mamalia',
            'jumlah_ekor' => 1,
            'kandang' => 'Kandang Timur',
            'status' => 'Dalam pemantauan',
        ]);
        $tanggal = now()->toDateString();

        $this->actingAs($admin)
            ->patch(route('admin.petugas.phone', $petugas), [
                'nomor_hp' => '0812-3456-7890',
            ])
            ->assertRedirect(route('admin.petugas.index'));

        $this->from(route('jadwal.index'))
            ->post(route('jadwal.acak'))
            ->assertRedirect(route('jadwal.index'))
            ->assertSessionHas('waDistributionLinks', function ($links) use ($petugas, $satwa) {
                return count($links) === 1
                    && $links[0]['petugas'] === $petugas->name
                    && $links[0]['satwa'] === $satwa->nama_satwa
                    && str_starts_with($links[0]['url'], 'https://wa.me/6281234567890?text=');
            });

        $message = "Halo Fadil, Anda mendapat tugas memberi makan satwa Harimau Malaya di Kandang Timur pada tanggal {$tanggal}. Mohon segera lakukan scan QR kandang. Terima kasih.";
        $whatsAppUrl = 'https://wa.me/6281234567890?text=' . rawurlencode($message);

        $this->get(route('jadwal.index'))
            ->assertOk()
            ->assertSee('Jadwal berhasil dibagikan')
            ->assertSee('Kirim WA ke Petugas')
            ->assertSee($whatsAppUrl, false);
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

    private function scanCheckpoint(JadwalPakan $jadwal, Satwa $satwa): void
    {
        $this->postJson(route('petugas.checkpoint'), [
            'task_id' => $jadwal->id,
            'qr_payload' => app(SatwaCheckpointQr::class)->payloadFor($satwa),
        ])
            ->assertOk()
            ->assertJsonPath('task_id', $jadwal->id);
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
