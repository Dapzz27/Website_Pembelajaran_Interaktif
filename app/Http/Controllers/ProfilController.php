<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function lihat()
    {
        $user = Auth::user();

        if ($user->role == 1) {
            return view('admin_guru.profil.lihatprofil', compact('user'));
        }

        if ($user->role == 0) {
            return view('siswa.profil.lihatprofil', compact('user'));
        }
    }

    public function edit()
    {
        $user = Auth::user();
        if ($user->role == 1) {
            return view('admin_guru.profil.editprofil', compact('user'));
        }
        if ($user->role == 0) {
            return view('siswa.profil.editprofil', compact('user'));
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'nomor_induk' => 'required|string|max:50|unique:users,nomor_induk,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;
        $user->nomor_induk = $request->nomor_induk;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {

            $folder = public_path('build/assets');

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            if ($user->foto && file_exists($folder . '/' . $user->foto)) {
                unlink($folder . '/' . $user->foto);
            }

            $file = $request->file('foto');

            $filename =
                'profil_' .
                $user->id .
                '_' .
                time() .
                '.' .
                $file->getClientOriginalExtension();

            $file->move($folder, $filename);

            $user->foto = $filename;
        }

        $user->save();

        return redirect()
            ->route($user->role == 1 ? 'guru.profil' : 'siswa.profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah, akun tidak dihapus.']);
        }

        $folder = public_path('build/assets');
        if ($user->foto && file_exists($folder . '/' . $user->foto)) {
            unlink($folder . '/' . $user->foto);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Akun berhasil dihapus.');
    }
}