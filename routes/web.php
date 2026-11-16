<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminPetugasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SatwaController;
use App\Http\Controllers\PetugasController;

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('login.store');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register');
Route::post('/register', [AuthController::class, 'register'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('register.store');

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [SatwaController::class, 'dashboard'])->name('dashboard');
    Route::get('/satwa', [SatwaController::class, 'index'])->name('satwa.index');
    Route::post('/satwa', [SatwaController::class, 'store'])->name('satwa.store');
    Route::put('/satwa/{satwa}', [SatwaController::class, 'update'])->name('satwa.update');
    Route::delete('/satwa/{satwa}', [SatwaController::class, 'destroy'])->name('satwa.destroy');
    Route::get('/riwayat', [SatwaController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [SatwaController::class, 'profil'])->name('profil');
    Route::get('/jadwal', [SatwaController::class, 'jadwalIndex'])->name('jadwal.index');
    Route::post('/jadwal/acak', [SatwaController::class, 'acakJadwal'])->name('jadwal.acak');
    Route::get('/dwh-analitik', [SatwaController::class, 'dwhIndex'])->name('dwh.index');
    Route::get('/admin/petugas', [AdminPetugasController::class, 'index'])->name('admin.petugas.index');
    Route::patch('/admin/petugas/{user}/status', [AdminPetugasController::class, 'updateStatus'])->name('admin.petugas.status');
});

Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('dashboard');
    Route::get('/satwa', [PetugasController::class, 'satwaIndex'])->name('satwa');
    Route::get('/riwayat', [PetugasController::class, 'riwayat'])->name('riwayat');
    Route::get('/profil', [PetugasController::class, 'profil'])->name('profil');
    Route::post('/laporan/{id}', [PetugasController::class, 'updateLaporan'])->name('laporan');
});