<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserController extends Controller
{
    public function index()
    {

       $currentUserId = Auth::id(); // Obtiene el ID del usuario autenticado

        $users = User::with('roles')
                    ->where('id', '!=', 4) // Excluye al usuario con ID 12
                    ->where('id', '!=', $currentUserId) // Excluye al usuario logueado
                    ->get();
        $roles = Role::all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

public function create()
{
    $roles = Role::all(['id', 'name']); // Traemos solo id y nombre

    return Inertia::render('Admin/Users/Create', [
        'roles' => $roles,
    ]);
}


public function store(Request $request)
{
    try {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles($validated['role_ids'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente');
    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
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
        $user->load(['roles:id,name']);
 

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::select('id', 'name')->get(),
            'user_roles' => $user->roles->pluck('id')->toArray(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Si vienen roles, sincroniza; si no vienen, elimina todos los roles del usuario.
        $user->syncRoles($validated['role_ids'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente');
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



}
