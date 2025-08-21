<?php

// app/Http/Controllers/Frontend/SecurityController.php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UpdateSecurityRequest;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(): Response
    {
        $user = auth()->user()->load('profile');

        // Lista de locales permitidos (label/value) para el select
        $locales = config('locales', [
            'es' => 'Español',
            'en' => 'English',
        ]);

        return Inertia::render('Frontend/Security/Edit', [
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'locale' => $user->locale,
            ],
            'locales' => $locales,
        ]);
    }

    public function update(UpdateSecurityRequest $request)
    {
        $user = $request->user();

        // Detect email change para (opcional) re-verificación
        $emailChanged = $request->email !== $user->email;

        $user->name   = $request->name;
        $user->email  = $request->email;
        $user->locale = $request->locale;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Si usas MustVerifyEmail y cambió el correo, puedes invalidar verificación:
        if (in_array(\Illuminate\Contracts\Auth\MustVerifyEmail::class, class_implements($user)) && $emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Si usas MustVerifyEmail y cambió el correo, reenvía verificación:
        if (in_array(\Illuminate\Contracts\Auth\MustVerifyEmail::class, class_implements($user)) && $emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Datos de acceso actualizados correctamente.');
    }
}
