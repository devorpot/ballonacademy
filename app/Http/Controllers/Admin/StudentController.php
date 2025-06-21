<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->get();

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
        $validated = $this->validateData($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('student');

        $user->student()->create($this->studentData($validated));

        return redirect()->route('admin.students.index')->with('success', 'Estudiante creado exitosamente');
    }

    public function edit(Student $student)
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student->load('user')
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $this->validateData($request, false, $student->user_id);

        $student->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => !empty($validated['password'])
                ? Hash::make($validated['password'])
                : $student->user->password,
        ]);

        $student->update($this->studentData($validated));

        return redirect()->route('admin.students.index')->with('success', 'Estudiante actualizado correctamente');
    }

    public function destroy(Student $student)
    {
        $student->user->delete();
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Estudiante eliminado exitosamente');
    }

    private function validateData(Request $request, $isStore = true, $userId = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255'],
            'password' => $isStore ? 'required|string|min:8|confirmed' : 'nullable|string|min:8|confirmed',
            'student_id' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'shirt_size' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
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
            'student_id' => $validated['student_id'],
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'phone' => $validated['phone'],
            'shirt_size' => $validated['shirt_size'],
            'address' => $validated['address'],
            'country' => $validated['country'],
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
            'student_id.required' => 'La matrícula es obligatoria.',
            'firstname.required' => 'El nombre del estudiante es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'phone.required' => 'El teléfono es obligatorio.',
            'shirt_size.required' => 'La talla de camiseta es obligatoria.',
            'address.required' => 'La dirección es obligatoria.',
            'country.required' => 'El país es obligatorio.',
        ];
    }
}
