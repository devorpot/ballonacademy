<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
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
            'nickname' => ['required', 'string'],
            'code'     => ['required', 'string', 'size:6'],
            'password' => [
                'required',
                'string',
                // Mínimo 8 caracteres, con letra, número y un símbolo
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $result = DB::transaction(function () use ($hash, $payload) {
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

            if (strcasecmp($payload['code'], $activation->code) !== 0) {
                throw ValidationException::withMessages([
                    'code' => 'El código de activación no coincide.',
                ]);
            }

            // Evita duplicar usuarios por email
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

            // Crear perfil con nickname (obligatorio desde el formulario)
            $user->profile()->create([
                'firstname' => $activation->name,
                'lastname'  => '',
                'nickname'  => $payload['nickname'],
                'phone'     => $activation->phone ?? '',
            ]);

            // Asignar curso si viene desde la activación
            $courseId = $activation->course_id;
            if ($courseId && !$user->courses()->where('courses.id', $courseId)->exists()) {
                $user->courses()->attach($courseId);
            }

            // Crear/confirmar suscripción gratuita por activación
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

            // Marcar activación como utilizada
            $activation->update([
                'active'  => true,
                'used_at' => now(),
            ]);

            return $user;
        });

        Auth::login($result);

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
        $payload = $request->session()->get('welcome');

        if (!$payload && Auth::check()) {
            $payload = [
                'name'      => Auth::user()->name,
                'email'     => Auth::user()->email,
                'dashboard' => route('dashboard.index'),
                'courses'   => route('dashboard.courses.index'),
            ];
        }

        if (!$payload) {
            return redirect()->route('login');
        }

        return Inertia::render('Public/Welcome', $payload);
    }
}
