<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.login');
    }

    /**
     * Proses login pengguna.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 6 karakter.',
        ]);

        $pengguna = User::where('email', $request->email)->first();

        if (!$pengguna || !Hash::check($request->password, $pengguna->kata_sandi)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        Auth::login($pengguna, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'))
                         ->with('sukses', 'Selamat datang kembali, ' . $pengguna->nama . '!');
    }

    /**
     * Tampilkan halaman registrasi.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.register');
    }

    /**
     * Proses registrasi pengguna baru.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama'             => 'required|string|max:100',
            'email'            => 'required|email|unique:pengguna,email',
            'password'         => 'required|string|min:6|confirmed',
            'departemen'       => 'nullable|string|max:100',
        ], [
            'nama.required'          => 'Nama wajib diisi.',
            'email.required'         => 'Email wajib diisi.',
            'email.unique'           => 'Email sudah terdaftar.',
            'password.required'      => 'Kata sandi wajib diisi.',
            'password.min'           => 'Kata sandi minimal 6 karakter.',
            'password.confirmed'     => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $pengguna = User::create([
            'nama'       => $request->nama,
            'email'      => $request->email,
            'kata_sandi' => Hash::make($request->password),
            'peran'      => 'user',
        ]);

        UserProfile::create([
            'pengguna_id' => $pengguna->id,
            'departemen'  => $request->departemen,
        ]);

        Auth::login($pengguna);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')
                         ->with('sukses', 'Akun berhasil dibuat. Selamat datang, ' . $pengguna->nama . '!');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                         ->with('sukses', 'Anda telah berhasil logout.');
    }
}
