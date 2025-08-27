<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PublicRegistrationController extends Controller
{
    public function show(string $hash)
    {
        $activation = Activation::where('hash', $hash)->first();

        if (!$activation) {
            abort(404);
        }

        return Inertia::render('Public/ActivationForm', [
            'name'  => $activation->name,
            'email' => $activation->email,
            'phone' => $activation->phone,
            'hash'  => $activation->hash,
        ]);
    }

    public function store(Request $request, string $hash)
    {
        $payload = $request->validate([
            'shirt_size' => ['required', Rule::in(['c','m','l','xl'])],
            'address'    => ['required', 'string', 'min:6', 'max:500'],
            'country'    => ['required', 'string', 'max:3'],
            'code'       => ['required', 'string', 'size:6'],
            'password'   => ['required','string','regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $result = DB::transaction(function () use ($hash, $payload) {
            // Bloquear activación para evitar condiciones de carrera
            $activation = Activation::where('hash', $hash)->lockForUpdate()->first();

            if (!$activation) {
                throw ValidationException::withMessages([
                    'code' => 'Activación no encontrada.',
                ]);
            }
            if ($activation->active) {
                throw ValidationException::withMessages([
                    'code' => 'Esta activación ya fue utilizada.',
                ]);
            }

            // Código debe coincidir (case-insensitive)
            if (strcasecmp($payload['code'], $activation->code) !== 0) {
                throw ValidationException::withMessages([
                    'code' => 'El código de activación no coincide.',
                ]);
            }

            // Email único en users (case-insensitive)
            if (User::whereRaw('LOWER(email) = ?', [mb_strtolower($activation->email)])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Este correo ya está registrado.',
                ]);
            }

            // Crear usuario
            $user = User::create([
                'name'     => $activation->name,
                'email'    => mb_strtolower($activation->email),
                'password' => Hash::make($payload['password']),
                'active'   => true,
                'locale'   => 'es',
            ]);
            $user->assignRole('student');

            // Perfil
            $user->profile()->create([
                'firstname' => $activation->name,
                'lastname'  => '',
                'nickname'  => null,
                'phone'     => $activation->phone ?? '',
                'country'   => $payload['country'] ?? '',
                'address'   => $payload['address'] ?? '',
                'shirt_size'=> $payload['shirt_size'] ?? null,
            ]);

            // Relación con curso (idempotente)
            $courseId = $activation->course_id;
            if ($courseId && ! $user->courses()->where('courses.id', $courseId)->exists()) {
                $user->courses()->attach($courseId);
            }

            // Suscripción idempotente por (user, course)
            Subscription::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $courseId],
                [
                    'payment_amount'      => 0,
                    'payment_currency'    => null,
                    'payment_description' => 'Alta por activación de cuenta',
                    'payment_type_id'     => null,
                    'payment_type'        => 'activation',
                    'payment_date'        => now(),
                    'payment_status'      => 'paid',
                    'payment_refund'      => false,
                    'used_coupon'         => false,
                ]
            );

            // Marcar activación como usada
            $activation->update([
                'active'   => true,
                'used_at'  => now(),  // si tienes la columna
            ]);

            return $user;
        });

        // Autologin (opcional; mejora UX)
        Auth::login($result);

        // Redirigir a pantalla de bienvenida
        return redirect()->route('public.register.welcome')
            ->with('welcome', [
                'name'      => $result->name,
                'email'     => $result->email,
                'dashboard' => route('dashboard.index'),
                'courses'   => route('dashboard.courses.index'),
            ]);
    }

    public function welcome(Request $request)
    {
        // Recupera datos desde flash o, si el user ya está logueado, arma props por defecto
        $payload = $request->session()->get('welcome');

        if (!$payload && Auth::check()) {
            $payload = [
                'name'      => Auth::user()->name,
                'email'     => Auth::user()->email,
                'dashboard' => route('dashboard.index'),
                'courses'   => route('dashboard.courses.index'),
            ];
        }

        // Si no hay nada que mostrar y no hay sesión, manda al login
        if (!$payload) {
            return redirect()->route('login');
        }

        return Inertia::render('Public/Welcome', $payload);
    }
}
