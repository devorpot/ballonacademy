<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
class AuthController extends Controller
{
    public function loginForm() {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function registerForm() {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name' => ['required'],
            'email' => ['required','email','unique:users'],
            'password' => ['required','min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }
}
