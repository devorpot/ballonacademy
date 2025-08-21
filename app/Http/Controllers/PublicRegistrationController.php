<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\User;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\Course;
use App\Models\Currency;
use App\Models\PaymentType;
use App\Models\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

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
        $activation = Activation::where('hash', $hash)->first();

        if (!$activation) {
            return back()->withErrors(['code' => 'Activación no encontrada.'])->withInput();
        }

        if ($activation->active) {
            return back()->withErrors(['code' => 'Esta activación ya fue utilizada.'])->withInput();
        }

        // Validaciones
        $validated = $request->validate([
            
            'shirt_size' => ['required', Rule::in(['c','m','l','xl'])],
            'address'    => ['required', 'string', 'min:6', 'max:500'],
            'country'    => ['required', 'string', 'max:3'],
            'code'       => ['required', 'string', 'min:6', 'max:6'],
            'password'   => [
                'required',
                'string',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            
            'shirt_size.required' => 'La talla es obligatoria',
            'shirt_size.in'       => 'Selecciona una talla válida',
            'address.required'    => 'La dirección es obligatoria',
            'country.required'    => 'El país es obligatorio',
            'code.required'       => 'El código es obligatorio',
            'code.min'            => 'El código debe tener 6 caracteres',
            'code.max'            => 'El código debe tener 6 caracteres',
            'password.required'   => 'La contraseña es obligatoria',
            'password.regex'      => 'La contraseña debe tener mínimo 8 caracteres, con letra, número y símbolo',
            'password_confirmation.required' => 'Repite la contraseña',
            'password_confirmation.same'     => 'Las contraseñas no coinciden',
        ]);

        // Comparar código con el de activations
        if (strcasecmp($validated['code'], $activation->code) !== 0) {
            return back()->withErrors(['code' => 'El código de activación no coincide.'])->withInput();
        }

        // Evitar duplicado de email
        if (User::where('email', $activation->email)->exists()) {
            return back()->withErrors(['email' => 'Este correo ya está registrado.'])->withInput();
        }

        // Crear usuario
        $user = User::create([
            'name'     => $activation->name,
            'email'    => $activation->email,
            'password' => Hash::make($validated['password']),
            'active'   => true,
            'locale'   => 'es',
        ]);

        // Rol student (Spatie)
        $user->assignRole('student');

        // Perfil
        $user->profile()->create([
            'firstname'      => $activation->name,
            'lastname'       => '',
            'nickname'       => null,
            'phone'          => $activation->phone ?? '',
            'country'        => $validated['country'] ?? '',
            'address'        => $validated['address'] ?? '',
            'profile_image'  => null,
            'bio'            => null,
        ]);

        // Suscripción y relación con curso
        $courseId = $activation->course_id;

        // Evitar duplicado de curso en pivote
        if (! $user->courses()->where('courses.id', $courseId)->exists()) {
            $user->courses()->attach($courseId);
        }

        // Crear Subscription con valores por defecto razonables
        // Ajusta las búsquedas por 'code' o 'name' según tu esquema real.
        $subscription = Subscription::create([
            'user_id'            => $user->id,
            'course_id'          => $courseId,
            'payment_amount'     => 0, // acceso por activación
            'payment_currency'   => null,
            'payment_description'=> 'Alta por activación de cuenta',
            'payment_type_id'    => null,
            'payment_type'       => 'activation',
            'payment_card'       => null,
            'payment_card_type'  => null,
            'payment_card_brand' => null,
            'payment_bank'       => null,
            'payment_date'       => now(),
            'payment_refund_date'=> null,
            'payment_refund_desc'=> null,
            'payment_status'     => 'paid', // o el texto que uses
            'payment_status_id'  => null,
            'payment_stripe_id'  => null,
            'payment_refund'     => false,
            'used_coupon'        => false,
            'coupon_id'          => null,
            'coupon_discount'    => null,
        ]);

        // Marcar activación como usada
        $activation->update([
            'active'       => true,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard.index')->with('success', '¡Registro completado exitosamente!');
    }
 
}
