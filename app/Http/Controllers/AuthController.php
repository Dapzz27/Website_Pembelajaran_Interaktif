<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return (int) Auth::user()->role === 1
                ? redirect()->route('guru.dashboard')
                : redirect()->route('siswa.dashboard');
        }

        return view('auth_login.login');
    }

    public function register()
    {
        if (Auth::check()) {
            return (int) Auth::user()->role === 1
                ? redirect()->route('guru.dashboard')
                : redirect()->route('siswa.dashboard');
        }

        return view('auth_login.register');
    }

    public function prosesLogin(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|string',
            'password' => 'required|string',
        ], [
            'nomor_induk.required' => 'NIS / NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $nomorInduk = trim($request->nomor_induk);
        $user = User::where('nomor_induk', $nomorInduk)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('nomor_induk'))
                ->with('error', 'NIS / NIP tidak ditemukan.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('nomor_induk'))
                ->with('error', 'Password salah.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ((int) $user->role === 1) {
            return redirect()->route('guru.dashboard');
        }

        return redirect()->route('siswa.dashboard');
    }

    public function prosesRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nomor_induk' => 'required|string|max:100|unique:users,nomor_induk',
            'email' => 'nullable|email|max:255|unique:users,email',
            'role' => 'nullable|in:0,1',
            'password' => 'required|string|confirmed|min:6',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nomor_induk.required' => 'NIS / NIP wajib diisi.',
            'nomor_induk.unique' => 'NIS / NIP sudah terdaftar, silakan gunakan nomor lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 6 karakter.',
        ]);

        User::create([
            'name' => trim($validated['name']),
            'nomor_induk' => trim($validated['nomor_induk']),
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'foto' => null,
            'role' => isset($validated['role']) ? (int) $validated['role'] : 0,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil, silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}