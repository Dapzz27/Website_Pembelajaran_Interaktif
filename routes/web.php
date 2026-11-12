<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\GuruQuizController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SiswaMateriController;
use App\Http\Controllers\SiswaQuizController;
use App\Http\Controllers\SiswaStatistikController;

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role == 1
            ? redirect()->route('guru.dashboard')
            : redirect()->route('siswa.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login-proses', [AuthController::class, 'prosesLogin'])->name('login.proses');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register-proses', [AuthController::class, 'prosesRegister'])->name('register.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard Guru
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin_guru')->name('guru.')->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('admin_guru.dashboard'))->name('dashboard');

    // Kelola Modul Pembelajaran (Tahap 1)
    Route::get('/modul/{modulId}', fn($modulId) => redirect()->route('guru.materi', ['modul_id' => $modulId]))->name('modul.show');
    Route::post('/modul/store', [MateriController::class, 'storeModul'])->name('modul.store');
    Route::put('/modul/{id}', [MateriController::class, 'updateModul'])->name('modul.update');
    Route::patch('/modul/{id}/urutan', [MateriController::class, 'updateUrutanModul'])->name('modul.urutan');
    Route::delete('/modul/{id}', [MateriController::class, 'destroyModul'])->name('modul.destroy');

    // Kelola Materi & Studi Kasus (Tahap 1: Daftar Modul | Tahap 2: Urutan Materi per Modul via ?modul_id=)
    Route::get('/materi', [MateriController::class, 'index'])->name('materi');
    Route::get('/materi/{id}', [MateriController::class, 'show'])->name('materi.show');
    Route::post('/materi/store', [MateriController::class, 'store'])->name('materi.store');
    Route::put('/materi/{id}', [MateriController::class, 'update'])->name('materi.update');
    Route::patch('/materi/{id}/urutan', [MateriController::class, 'updateUrutan'])->name('materi.urutan');
    Route::delete('/materi/{id}', [MateriController::class, 'destroy'])->name('materi.destroy');

    // Kelola Quiz & Soal
    Route::get('/quiz', [GuruQuizController::class, 'index'])->name('quiz');
    Route::post('/quiz', [GuruQuizController::class, 'store'])->name('quiz.store');
    Route::put('/quiz/{id}', [GuruQuizController::class, 'update'])->name('quiz.update');
    Route::delete('/quiz/{id}', [GuruQuizController::class, 'destroy'])->name('quiz.destroy');
    Route::post('/quiz/{quizId}/soal', [GuruQuizController::class, 'storeQuestion'])->name('quiz.soal.store');
    Route::put('/quiz/{quizId}/soal/{questionId}', [GuruQuizController::class, 'updateQuestion'])->name('quiz.soal.update');
    Route::delete('/quiz/{quizId}/soal/{questionId}', [GuruQuizController::class, 'destroyQuestion'])->name('quiz.soal.destroy');

    // Data Siswa & Statistik
    Route::get('/data_siswa', fn() => view('admin_guru.data_siswa'))->name('data_siswa');
    Route::get('/statistik', fn() => view('admin_guru.statistik'))->name('statistik');

    // Profil Guru
    Route::get('/profil', [ProfilController::class, 'lihat'])->name('profil');
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');
});

/*
|--------------------------------------------------------------------------
| Dashboard Siswa
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('siswa')->name('siswa.')->group(function () {

    // Dashboard Siswa
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    // Katalog Modul & Urutan Materi Siswa
    Route::get('/modul/{modulId}', fn($modulId) => redirect()->route('siswa.materi', ['modul_id' => $modulId]))->name('modul.show');
    Route::get('/materi', [SiswaMateriController::class, 'index'])->name('materi');
    Route::get('/materi/{id}', [SiswaMateriController::class, 'show'])->name('materi.show');
    Route::post('/materi/{id}/track-baca', [SiswaMateriController::class, 'trackBaca'])->name('materi.track_baca');
    Route::post('/materi/{id}/baca', [SiswaMateriController::class, 'tandaiBaca'])->name('materi.baca');
    Route::post('/materi/{id}/selesaikan', [SiswaMateriController::class, 'selesaikanMisi'])->name('materi.selesaikan');

    // Quiz Siswa
    Route::get('/quiz', [SiswaQuizController::class, 'index'])->name('quiz');
    Route::post('/quiz/{id}/submit', [SiswaQuizController::class, 'submit'])->name('quiz.submit');

    // Statistik Belajar Siswa
    Route::get('/statistik', [SiswaStatistikController::class, 'index'])->name('statistik');

    // Profil Siswa
    Route::get('/profil', [ProfilController::class, 'lihat'])->name('profil');
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');
});
