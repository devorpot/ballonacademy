<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(): Response
    {
        $teachers = Teacher::with(['user.roles'])->get();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Teachers/Create');
    }

    public function store(Request $request)
    {
        $data = $this->validateStore($request);

        return DB::transaction(function () use ($request, $data) {
            // Crear usuario
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('teacher');

            // Preparar payload de Teacher
            $payload = [
                'firstname' => $data['firstname'],
                'lastname'  => $data['lastname'],
                'phone'     => $data['phone'],
                'specialty' => $data['specialty'],
                'country'   => $data['country'],

                'facebook'  => $data['facebook']  ?? null,
                'instagram' => $data['instagram'] ?? null,
                'tiktok'    => $data['tiktok']    ?? null,
                'website'   => $data['website']   ?? null,

                'profile_image' => null,
                'cover_image'   => null,
            ];

            // Uploads (si llegan archivos)
            if ($request->hasFile('profile_image')) {
                $payload['profile_image'] = $request->file('profile_image')
                    ->store('teachers/profile_images', 'public');
            }
            if ($request->hasFile('cover_image')) {
                $payload['cover_image'] = $request->file('cover_image')
                    ->store('teachers/cover_images', 'public');
            }

            $user->teacher()->create($payload);

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'Maestro creado exitosamente');
        });
    }

    public function edit(Teacher $teacher): Response
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher->load(['user.roles']),
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $this->validateUpdate($request, $teacher);

        return DB::transaction(function () use ($request, $teacher, $data) {
            // Actualizar User
            $teacher->user->fill([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);
            if (!empty($data['password'])) {
                $teacher->user->password = Hash::make($data['password']);
            }
            $teacher->user->save();

            // Payload base de Teacher
            $payload = [
                'firstname' => $data['firstname'],
                'lastname'  => $data['lastname'],
                'phone'     => $data['phone'],
                'specialty' => $data['specialty'],
                'country'   => $data['country'],

                'facebook'  => $data['facebook']  ?? null,
                'instagram' => $data['instagram'] ?? null,
                'tiktok'    => $data['tiktok']    ?? null,
                'website'   => $data['website']   ?? null,
            ];

            // Eliminar imágenes existentes si se solicita
            if ($request->boolean('remove_profile_image') && $teacher->profile_image) {
                Storage::disk('public')->delete($teacher->profile_image);
                $payload['profile_image'] = null;
            }
            if ($request->boolean('remove_cover_image') && $teacher->cover_image) {
                Storage::disk('public')->delete($teacher->cover_image);
                $payload['cover_image'] = null;
            }

            // Reemplazar con nuevos uploads (borra anterior si existe)
            if ($request->hasFile('profile_image')) {
                if ($teacher->profile_image) {
                    Storage::disk('public')->delete($teacher->profile_image);
                }
                $payload['profile_image'] = $request->file('profile_image')
                    ->store('teachers/profile_images', 'public');
            }

            if ($request->hasFile('cover_image')) {
                if ($teacher->cover_image) {
                    Storage::disk('public')->delete($teacher->cover_image);
                }
                $payload['cover_image'] = $request->file('cover_image')
                    ->store('teachers/cover_images', 'public');
            }

            $teacher->update($payload);

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'Maestro actualizado correctamente');
        });
    }

    public function destroy(Teacher $teacher)
    {
        // Borra imágenes asociadas
        if ($teacher->profile_image) {
            Storage::disk('public')->delete($teacher->profile_image);
        }
        if ($teacher->cover_image) {
            Storage::disk('public')->delete($teacher->cover_image);
        }

        $teacher->user->delete();
        $teacher->delete();

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Maestro eliminado exitosamente');
    }

    public function show(Teacher $teacher): Response
    {
        return Inertia::render('Admin/Teachers/Show', [
            'teacher' => $teacher->load(['user.roles']),
        ]);
    }

    public function list()
    {
        $teachers = Teacher::with('user')
            ->get()
            ->map(fn ($t) => [
                'id'   => $t->id,
                'name' => trim("{$t->firstname} {$t->lastname}"),
            ]);

        return response()->json($teachers);
    }

    /**
     * -------- VALIDACIONES --------
     */
    private function validateStore(Request $request): array
    {
        return $request->validate(
            [
                // User
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],

                // Teacher
                'firstname' => ['required', 'string', 'max:255'],
                'lastname'  => ['required', 'string', 'max:255'],
                'phone'     => ['required', 'string', 'max:255'],
                'specialty' => ['required', 'string', 'max:255'],
                'country'   => ['required', 'string', 'max:255'],

                // Redes / sitio
                'facebook'  => ['nullable', 'url', 'max:255'],
                'instagram' => ['nullable', 'url', 'max:255'],
                'tiktok'    => ['nullable', 'url', 'max:255'],
                'website'   => ['nullable', 'url', 'max:255'],

                // Imágenes
                'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'cover_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ],
            $this->validationMessages()
        );
    }

    private function validateUpdate(Request $request, Teacher $teacher): array
    {
        return $request->validate(
            [
                // User
                'name'  => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 'email', 'max:255',
                    Rule::unique('users', 'email')->ignore($teacher->user->id),
                ],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],

                // Teacher
                'firstname' => ['required', 'string', 'max:255'],
                'lastname'  => ['required', 'string', 'max:255'],
                'phone'     => ['required', 'string', 'max:255'],
                'specialty' => ['required', 'string', 'max:255'],
                'country'   => ['required', 'string', 'max:255'],

                // Redes / sitio
                'facebook'  => ['nullable', 'url', 'max:255'],
                'instagram' => ['nullable', 'url', 'max:255'],
                'tiktok'    => ['nullable', 'url', 'max:255'],
                'website'   => ['nullable', 'url', 'max:255'],

                // Imágenes + flags de eliminación
                'profile_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'cover_image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'remove_profile_image' => ['sometimes', 'boolean'],
                'remove_cover_image'   => ['sometimes', 'boolean'],
            ],
            $this->validationMessages()
        );
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

            'firstname.required'  => 'El nombre del maestro es obligatorio.',
            'lastname.required'   => 'El apellido es obligatorio.',
            'phone.required'      => 'El teléfono es obligatorio.',
            'specialty.required'  => 'La especialidad es obligatoria.',
            'country.required'    => 'El país es obligatorio.',

            'facebook.url'        => 'Facebook debe ser una URL válida.',
            'instagram.url'       => 'Instagram debe ser una URL válida.',
            'tiktok.url'          => 'TikTok debe ser una URL válida.',
            'website.url'         => 'El sitio web debe ser una URL válida.',

            'profile_image.image' => 'La imagen de perfil debe ser una imagen.',
            'profile_image.mimes' => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'profile_image.max'   => 'La imagen de perfil no puede superar 2MB.',
            'cover_image.image'   => 'La imagen de portada debe ser una imagen.',
            'cover_image.mimes'   => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'cover_image.max'     => 'La imagen de portada no puede superar 4MB.',
        ];
    }
}
