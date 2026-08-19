<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        //untuk mengarahkan ke halaman login
        if (Auth::check()) {
            $role = Auth::user()->role;

            if ($role === 'admin') {
                return redirect()->back();
            }
        }

        return view('components.auth.sign_in');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Login Berhasil');
            }

            // Tidak redirect jika bukan admin
            return back()->with('error', 'Email atau password salah');
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function lockscreen()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        session(['locked' => true]);

        return view('components.auth.lock-screen', compact('user'));
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah.');
        }

        $redirect = session('locked_redirect') ?? route('admin.dashboard');

        session()->forget('locked');
        session()->forget('locked_redirect');
        session()->save();

        return redirect($redirect)->with('success', 'Selamat Datang Kembali!');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }
}
