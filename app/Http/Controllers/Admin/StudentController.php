<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        // Carga también profile si lo vas a mostrar en tabla/lista
        $students = Student::with(['user.profile'])->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Students/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request, true);

        // Crear usuario
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'active'   => (bool)($validated['active'] ?? false),
            'locale'   => $request->input('locale', 'es'), // opcional, por si decides permitirlo en el form
        ]);

        // Rol
        $user->assignRole('student');

        // Crear estudiante
        $user->student()->create($this->studentData($validated));

        // Crear profile (para evitar null en Sidebar/TopNav)
        // Puedes mapear aquí lo básico con los mismos datos del student
        $user->profile()->create([
            'firstname'      => $validated['firstname'],
            'lastname'       => $validated['lastname'],
            'nickname'       => $this->guessNickname($user),
            'phone'          => $validated['phone'] ?? '',
            'country'        => $validated['country'] ?? '',
            'address'        => $validated['address'] ?? '',
            'profile_image'  => null, // se cargará más tarde
            'bio'            => null,
        ]);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Estudiante creado exitosamente');
    }

    public function edit(Student $student)
    {
        $student->load(['user.profile']);

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $this->validateData($request, false, $student->user_id);

        // Actualizar usuario
        $student->user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'active'   => (bool)($validated['active'] ?? false),
            'password' => !empty($validated['password'])
                ? Hash::make($validated['password'])
                : $student->user->password,
        ]);

        // Actualizar datos del estudiante
        $student->update($this->studentData($validated));

        // Asegurar que exista profile antes de delegar a ProfileController
        if (! $student->user->profile) {
            $student->user->profile()->create([
                'firstname'      => $validated['firstname'],
                'lastname'       => $validated['lastname'],
                'nickname'       => $this->guessNickname($student->user),
                'phone'          => $validated['phone'] ?? '',
                'country'        => $validated['country'] ?? '',
                'address'        => $validated['address'] ?? '',
                'profile_image'  => null,
                'bio'            => null,
            ]);
            // refrescar la relación para que el ProfileController la encuentre
            $student->user->load('profile');
        }

        // Reutilizar ProfileController (asumiendo que espera campos en el mismo request)
        // Si tu ProfileController espera nombres de campos distintos, asegúrate de enviarlos.
        app(\App\Http\Controllers\Admin\ProfileController::class)->update($request, $student->user);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Estudiante actualizado correctamente');
    }

    public function destroy(Student $student)
    {
        // Asumiendo que User usa SoftDeletes, esto soft-deletea al usuario.
        $student->user->delete();
        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Estudiante eliminado exitosamente');
    }

    public function list()
    {
        $students = Student::with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id'   => $student->user->id,
                    'name' => trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? '')),
                ];
            });

        return response()->json($students);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        $students = Student::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(fn ($student) => [
                'label' => "{$student->user->name} ({$student->user->email})",
                'value' => $student->user_id
            ]);

        return response()->json($students);
    }

    public function searchById($id)
    {
        $student = Student::with('user')->where('user_id', $id)->firstOrFail();

        return response()->json([
            'value' => $student->user_id,
            'label' => "{$student->user->name} ({$student->user->email})"
        ]);
    }

    private function validateData(Request $request, $isStore = true, $userId = null): array
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'email'      => ['required', 'email', 'max:255'],
            'password'   => $isStore ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
            'firstname'  => 'required|string|max:255',
            'lastname'   => 'required|string|max:255',
            'phone'      => 'required|string|max:255',
            'shirt_size' => 'required|string|max:255',
            'address'    => 'required|string|max:255',
            'country'    => 'required|string|max:255',
            'active'     => 'required|boolean',
            // 'locale'  => 'nullable|in:es,en' // si decides exponerlo en el formulario
        ];

        if ($userId) {
            $rules['email'][] = 'unique:users,email,' . $userId;
        } else {
            $rules['email'][] = 'unique:users,email';
        }

        return $request->validate($rules, $this->validationMessages());
    }

    private function studentData(array $validated): array
    {
        return [
            'firstname'  => $validated['firstname'],
            'lastname'   => $validated['lastname'],
            'phone'      => $validated['phone'],
            'shirt_size' => $validated['shirt_size'],
            'address'    => $validated['address'],
            'country'    => $validated['country'],
        ];
    }

    private function guessNickname(User $user): string
    {
        if ($user->email) {
            return explode('@', $user->email)[0];
        }
        return str($user->name)->slug('_')->value();
    }

    private function validationMessages(): array
    {
        return [
            'name.required'       => 'El nombre es obligatorio.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'email.email'         => 'El correo debe tener un formato válido.',
            'email.unique'        => 'Este correo ya está registrado.',
            'password.required'   => 'La contraseña es obligatoria.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
            'firstname.required'  => 'El nombre del estudiante es obligatorio.',
            'lastname.required'   => 'El apellido es obligatorio.',
            'phone.required'      => 'El teléfono es obligatorio.',
            'shirt_size.required' => 'La talla de camiseta es obligatoria.',
            'address.required'    => 'La dirección es obligatoria.',
            'country.required'    => 'El país es obligatorio.',
            'active.required'     => 'El estado activo/inactivo es obligatorio.',
            'active.boolean'      => 'El estado activo debe ser verdadero o falso.',
        ];
    }
}
