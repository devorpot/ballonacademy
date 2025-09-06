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
                'active'   => $request->boolean('user_active', true),
                'locale'   => $request->string('user_locale')->toString() ?: 'es',
            ]);
            $user->assignRole('teacher');

            // Payload base Teacher
            $payload = [
                'firstname' => $data['firstname'],
                'lastname'  => $data['lastname'],
                'phone'     => $data['phone'],
                'specialty' => $data['specialty'],
                'country'   => $data['country'],
                'address'   => $data['address'] ?? null,

                'facebook'  => $data['facebook']  ?? null,
                'instagram' => $data['instagram'] ?? null,
                'tiktok'    => $data['tiktok']    ?? null,
                'website'   => $data['website']   ?? null,
                'youtube'   => $data['youtube']   ?? null,

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
            // Soportar ambos nombres de campos (user_* y legacy)
            $userName  = $request->input('user_name', $request->input('name', $teacher->user->name));
            $userEmail = $request->input('user_email', $request->input('email', $teacher->user->email));

            // Actualizar User
            $teacher->user->fill([
                'name'   => $userName,
                'email'  => $userEmail,
                'active' => $request->has('user_active')
                    ? $request->boolean('user_active')
                    : ($teacher->user->active ?? true),
                'locale' => $request->filled('user_locale')
                    ? (string) $request->input('user_locale')
                    : ($teacher->user->locale ?? 'es'),
            ]);

            // Password opcional (si llega en alguna forma vieja)
            if (!empty($data['password'] ?? null)) {
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
                'address'   => $data['address'] ?? null,

                'facebook'  => $data['facebook']  ?? null,
                'instagram' => $data['instagram'] ?? null,
                'tiktok'    => $data['tiktok']    ?? null,
                'website'   => $data['website']   ?? null,
                'youtube'   => $data['youtube']   ?? null,
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
        // User (creación)
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],

        // Opcionales de cuenta en create
        'user_active' => ['sometimes', 'boolean'],
        'user_locale' => ['sometimes', 'string', 'max:10'],

        // Teacher  (AQUÍ: requeridos para empatar con el front)
        'firstname' => ['required', 'string', 'max:255'],
        'lastname'  => ['required', 'string', 'max:255'],
        'phone'     => ['nullable', 'string', 'max:255'],     // antes: nullable
        'specialty' => ['nullable', 'string', 'max:255'],     // antes: nullable
        'country'   => ['required', 'string', 'max:255'],     // antes: nullable
        'address'   => ['nullable', 'string', 'max:500'],

        // Redes / sitio
        'facebook'  => ['nullable', 'url', 'max:255'],
        'instagram' => ['nullable', 'url', 'max:255'],
        'tiktok'    => ['nullable', 'url', 'max:255'],
        'website'   => ['nullable', 'url', 'max:255'],
        'youtube'   => ['nullable', 'url', 'max:255'],

        // Imágenes
        'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'cover_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
    ],
    $this->validationMessages()
);
    }

    private function validateUpdate(Request $request, Teacher $teacher): array
    {
        // Notas: permitimos user_* y legacy para compatibilidad.
        // Mapeo previo no es necesario aquí porque validamos ambos.
       return $request->validate(
    [
        // User (edición)
        'user_name' => ['sometimes', 'required', 'string', 'max:255'],
        'user_email' => [
            'sometimes', 'required', 'email', 'max:255',
            Rule::unique('users', 'email')->ignore($teacher->user->id),
        ],
        // Compat legacy:
        'name'  => ['sometimes', 'required', 'string', 'max:255'],
        'email' => [
            'sometimes', 'required', 'email', 'max:255',
            Rule::unique('users', 'email')->ignore($teacher->user->id),
        ],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'], // opcional

        'user_active' => ['sometimes', 'boolean'],
        'user_locale' => ['sometimes', 'string', 'max:10'],

        // Teacher (AQUÍ: requeridos para empatar con el front)
        'firstname' => ['required', 'string', 'max:255'],
        'lastname'  => ['required', 'string', 'max:255'],
        'phone'     => ['nullable', 'string', 'max:255'],     // antes: nullable
        'specialty' => ['nullable', 'string', 'max:255'],     // antes: nullable
        'country'   => ['required', 'string', 'max:255'],     // antes: nullable
        'address'   => ['nullable', 'string', 'max:500'],

        // Redes / sitio
        'facebook'  => ['nullable', 'url', 'max:255'],
        'instagram' => ['nullable', 'url', 'max:255'],
        'tiktok'    => ['nullable', 'url', 'max:255'],
        'website'   => ['nullable', 'url', 'max:255'],
        'youtube'   => ['nullable', 'url', 'max:255'],

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
            'user_name.required'  => 'El nombre de cuenta es obligatorio.',
            'email.required'      => 'El correo electrónico es obligatorio.',
            'user_email.required' => 'El correo electrónico de cuenta es obligatorio.',
            'email.email'         => 'El correo debe tener un formato válido.',
            'user_email.email'    => 'El correo debe tener un formato válido.',
            'email.unique'        => 'Este correo ya está registrado.',
            'user_email.unique'   => 'Este correo ya está registrado.',
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
            'youtube.url'         => 'YouTube debe ser una URL válida.',

            'profile_image.image' => 'La imagen de perfil debe ser una imagen.',
            'profile_image.mimes' => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'profile_image.max'   => 'La imagen de perfil no puede superar 2MB.',
            'cover_image.image'   => 'La imagen de portada debe ser una imagen.',
            'cover_image.mimes'   => 'Formatos permitidos: jpg, jpeg, png, webp.',
            'cover_image.max'     => 'La imagen de portada no puede superar 4MB.',
        ];
    }
}
