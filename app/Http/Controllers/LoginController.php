<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('Login.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasRole('superadmin')) {
                return redirect()->route('dashboard'); // Dashboard Admin
            }

            if ($user->hasRole('admin')) {
                return redirect()->route('mitra.dashboard'); // Dashboard Mitra
            }

            // Jika login berhasil tapi role tidak valid
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'role' => 'Akun Anda tidak memiliki role yang sesuai.',
            ]);
        }

        // Jika email/password salah
        return back()->withErrors([
            'email' => 'Login gagal: email atau password salah.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
