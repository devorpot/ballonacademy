<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ActivationInvitationMail;
use App\Models\Activation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivationsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'min:3', 'max:120'],
            'email'      => ['required', 'email', 'max:190'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'course_id'  => ['required', 'integer', 'exists:courses,id'], // NUEVO
        ], [
            'course_id.required' => 'El curso es obligatorio.',
            'course_id.exists'   => 'El curso seleccionado no existe.',
        ]);

        // Verificar que email NO exista en users ni en activations
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'El email ya existe en usuarios.',
                'errors'  => ['email' => ['El email ya está registrado en usuarios.']]
            ], 422);
        }

        if (Activation::where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'El email ya cuenta con una activación generada.',
                'errors'  => ['email' => ['El email ya está en activaciones.']]
            ], 422);
        }

        // Generar código único (3 números + 3 letras) en orden mezclado
        $code = $this->generateUniqueCode();

        // Generar hash seguro con name + email + salt efímero
        $nonce = Str::random(16);
        $hash  = hash_hmac('sha256', $validated['name'].'|'.$validated['email'].'|'.$nonce, config('app.key'));

        // Enlace público completo
        $link = URL::to('/register/student/'.$hash);

        // Guardar registro
        $activation = Activation::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'course_id' => $validated['course_id'], // NUEVO
            'code'      => $code,
            'hash'      => $hash,
            'active'    => false,
            'created'   => now(),
        ]);

        // Enviar correo
        Mail::to($validated['email'])->send(
            new ActivationInvitationMail(
                name: $validated['name'],
                link: $link,
                code: $code
            )
        );

        return response()->json([
            'ok' => true,
            'activation' => [
                'hash' => $hash,
                'code' => $code,
                'link' => $link,
            ],
        ], 201);
    }

    private function generateUniqueCode(): string
    {
        do {
            $numbers = strval(random_int(100, 999));  // 3 dígitos
            $letters = '';
            for ($i=0; $i<3; $i++) {
                $letters .= chr(random_int(65, 90)); // A-Z
            }
            $chars = str_split($numbers.$letters);
            shuffle($chars);
            $code = implode('', $chars);
        } while (Activation::where('code', $code)->exists());

        return $code;
    }
}
