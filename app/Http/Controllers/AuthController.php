<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->route($this->dashboardRoute($request->user()));
        }

        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->role = 'petugas';
        $user->is_active = false;
        $user->save();

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran berhasil. Akun Anda menunggu persetujuan Admin.');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi tidak valid.'])
                ->onlyInput('email');
        }

        $user = $request->user();
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->approved_at
                ? 'Akun Anda sedang dinonaktifkan. Hubungi Admin untuk informasi lebih lanjut.'
                : 'Akun Anda belum aktif. Hubungi Admin untuk mendapatkan persetujuan.';

            return redirect()
                ->route('login')
                ->withErrors(['email' => $message])
                ->withInput(['email' => $credentials['email']]);
        }

        $request->session()->regenerate();

        if (! in_array($user->role, ['admin', 'petugas'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Akun ini belum memiliki peran yang diizinkan.'])
                ->onlyInput('email');
        }

        return redirect()->route($this->dashboardRoute($user));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(User $user): string
    {
        return $user->role === 'admin' ? 'dashboard' : 'petugas.dashboard';
    }
}
