<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
 
use Illuminate\Http\Request;
use App\Models\User;
 
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\Student;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;





class UserController extends Controller
{
    public function index()
    {

       $currentUserId = Auth::id(); // Obtiene el ID del usuario autenticado

        $users = User::with('roles')
                    ->where('id', '!=', 4) 
                    ->where('id', '!=', $currentUserId)  
                    ->get();
        $roles = Role::all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $roles = Role::all(['id', 'name','label']); 

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }


 
public function store(Request $request)
{
    try {
        $validated = $request->validate(
            array_merge($this->validationRules(), [
                'active' => 'required|boolean',
            ]),
            $this->validationMessages()
        );

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active'   => false, 
        ]);

        $user->syncRoles($validated['role_ids'] ?? []);

        // Obtener los nombres de los roles asignados
        $roleNames = Role::whereIn('id', $validated['role_ids'] ?? [])->pluck('name')->toArray();

        // Si tiene rol de maestro, crear perfil de maestro si no existe
        if (in_array('teacher', $roleNames) && !$user->teacher) {
            $user->teacher()->create([
                'firstname' => $user->name,
                'lastname'  => '',
                'phone'     => '',
                'specialty' => '',
                'address'   => '',
                'country'   => '',
            ]);
        }

        // Si tiene rol de estudiante, crear perfil de estudiante si no existe
        if (in_array('student', $roleNames) && !$user->student) {
            $user->student()->create([
                'firstname'  => $user->name,
                'lastname'   => '',
                'phone'      => '',
                'shirt_size' => '',
                'address'    => '',
                'country'    => '',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente');
    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $e->errors(),
            ], 422);
        }
        throw $e;
    }
}



   public function show(User $user)
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load('roles'),
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all(['id', 'name', 'label']); 

 

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::select('id', 'name','label')->get(),
            'user_roles' => $user->roles->pluck('id')->toArray(),
        ]);
    }

  public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
        'password'   => 'nullable|string|min:8|confirmed',
        'role_ids'   => 'nullable|array',
        'role_ids.*' => 'exists:roles,id',
        'active'     => 'required|boolean', // ← validar el switch
    ]);

    $updateData = [
        'name'   => $validated['name'],
        'email'  => $validated['email'],
        'active' => (bool) ($validated['active'] ?? false),  
    ];

    if (!empty($validated['password'])) {
        $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
    }

    $user->update($updateData);

    // Sincronizar roles (si no vienen, se limpia)
    $user->syncRoles($validated['role_ids'] ?? []);

    // Crear perfiles si ahora tiene el rol y no existe el perfil
    $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $validated['role_ids'] ?? [])
        ->pluck('name')
        ->toArray();

    if (in_array('teacher', $roleNames) && !$user->teacher) {
        $user->teacher()->create([
            'firstname' => $user->name,
            'lastname'  => '',
            'phone'     => '',
            'specialty' => '',
            'address'   => '',
            'country'   => '',
        ]);
    }

    if (in_array('student', $roleNames) && !$user->student) {
        $user->student()->create([
            'firstname'  => $user->name,
            'lastname'   => '',
            'phone'      => '',
            'shirt_size' => '',
            'address'    => '',
            'country'    => '',
        ]);
    }

    return redirect()
        ->route('admin.users.index')
        ->with('success', 'Usuario actualizado correctamente');
}


    public function destroy(User $user)
    {
        $user->delete(); 

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente');
    }


    private function validationRules(User $user = null): array
        {
            $userId = $user ? $user->id : 'NULL';

            return [
                'name' => 'required|string|max:255',
                'email' => "required|email|max:255|unique:users,email,{$userId}",
                'password' => $user ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
                'role_ids' => 'nullable|array',
                'role_ids.*' => 'exists:roles,id',
            ];
        }

        private function validationMessages(): array
        {
            return [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo debe tener un formato válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'role_ids.*.exists' => 'Uno o más roles seleccionados no son válidos.',
            ];
        }


 

       public function updateLocale(Request $request)
            {
                $request->validate([
                    'locale' => ['required', 'string', 'in:es,en'] // ajusta según idiomas permitidos
                ]);

                $user = auth()->user();
                $user->locale = $request->locale;
                $user->save();

                return response()->json(['success' => true]);
         }

 public function sendActivation(Request $request, User $user)
{
    if ($user->active) {
        return back()->with('error', 'El usuario ya está activo.');
    }

    $validated = $request->validate([
        'assign_password' => ['required','boolean'],
        'password'        => ['nullable','string','min:10'],
    ], [
        'password.min' => 'El password debe tener al menos 10 caracteres.',
    ]);

    // Determinar password
    $passwordToSet = $validated['assign_password']
        ? ($validated['password'] ?? null)
        : $this->generatePassword(12);

    if (!$passwordToSet) {
        return back()->withErrors(['password' => 'Debe indicar el password o desactivar "Asignar password manualmente".']);
    }

    // Actualizar password del usuario
    $user->password = Hash::make($passwordToSet);
    $user->save();

    // URL firmada de activación (48 horas)
    $signedUrl = URL::temporarySignedRoute(
        'users.activate',
        now()->addHours(48),
        ['user' => $user->id]
    );

    // Enviar correo al email del usuario registrado
    $subject = 'Bienvenido(a) a Ballon Academy - Activa tu cuenta';

    Mail::send('emails.activation-basic', [
        'user'          => $user,
        'activationUrl' => $signedUrl,
        'password'      => $passwordToSet,
    ], function ($message) use ($user, $subject) {
        $message->to($user->email)
                ->subject($subject);
    });

    return back()->with('success', 'Se envió el correo de activación al usuario.');
}

    public function activate(Request $request, User $user)
    {


        if (! $request->hasValidSignature()) {
            abort(401, 'Enlace de activación inválido o expirado.');
        }

        if (! $user->active) {
            $user->active = true;
            $user->email_verified_at = now(); // opcional
            $user->save();
        }

        return redirect()->route('login')->with('success', 'Tu cuenta ha sido activada. Ya puedes iniciar sesión.');
    }

protected function generatePassword(int $length = 10): string
{
    $pool = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%?-_';
    $pwd = '';
    for ($i = 0; $i < $length; $i++) {
        $pwd .= $pool[random_int(0, strlen($pool) - 1)];
    }
    return $pwd;
}




}
