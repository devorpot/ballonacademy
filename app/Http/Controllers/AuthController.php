<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

// Para generación de activaciones
use Illuminate\Support\Str;
use App\Models\Activation;
use App\Models\Course;

class AuthController extends Controller
{
    // Mostrar formulario de login o redirigir si ya está autenticado
    public function loginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

             if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('student')) {
                return redirect()->route('dashboard.index');
            }

            // Si no tiene rol válido, cerrar sesión por seguridad
            Auth::logout();
            return redirect()->route('login');
        }

        return Inertia::render('Auth/Login');
    }

    // Procesar login
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::user();

        // Flash solo para la siguiente petición
        session()->flash(
            'welcome_message',
            'Bienvenido a la Academia Internacional de Globos' // o "Bienvenido, '.$user->name.'"
        );

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('dashboard.index');
        }

        Auth::logout();
        return back()->withErrors([
            'email' => 'Tu cuenta no tiene acceso autorizado.',
        ]);
    }

    return back()->withErrors([
        'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
    ]);
}

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Mostrar formulario de recuperación (solicitar correo)
    public function forgotPasswordForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // Enviar email de recuperación
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // Formulario para nueva contraseña (token)
    public function showResetForm(Request $request, $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // Guardar nueva contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    // Cambiar contraseña (usuario autenticado)
    public function changePasswordForm()
    {
        return Inertia::render('Auth/ChangePassword');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $request->session()->regenerate();

        return back()->with('status', 'Contraseña actualizada correctamente.');
    }

    // Generar token de activación (para crear un registro en activations)
    public function activationTokenForm()
    {
        return Inertia::render('Auth/ActivationToken', [
            // opcionalmente puedes pasar lista de cursos
            // 'courses' => Course::select('id','title')->orderBy('title')->get(),
        ]);
    }

    public function createActivationToken(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email'],
            'phone'     => ['nullable','string','max:50'],
            'course_id' => ['nullable','integer','exists:courses,id'],
        ]);

        // Generar código de 6 caracteres y hash único
        $code = strtoupper(Str::random(6));
        $hash = Str::uuid()->toString();

        $activation = Activation::create([
            'name'      => $data['name'],
            'email'     => mb_strtolower($data['email']),
            'phone'     => $data['phone'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'code'      => $code,
            'hash'      => $hash,
            'active'    => false,
        ]);

        // Aquí podrías disparar un mail si ya lo tienes configurado
        // Mail::to($activation->email)->send(new ActivationInvitationMail($activation));

        return back()->with('activation', [
            'code' => $activation->code,
            'hash' => $activation->hash,
            // Ajusta esta ruta a la de tu formulario de activación pública
            // 'url'  => route('public.activation.show', ['hash' => $activation->hash]),
        ]);
    }
}
