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

            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                return redirect()->route('dashboard');
            }

            Auth::logout();
            return redirect()->route('login')->withErrors([
                'role' => 'Akun Anda tidak memiliki akses yang sesuai.',
            ]);
        }

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
