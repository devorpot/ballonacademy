<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Course;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Listado de estudiantes con búsqueda, filtros y ordenación.
     */
    public function index(Request $request): Response
    {
        $q        = trim((string) $request->query('q', ''));
        $country  = $request->query('country');
        $active   = $request->query('active'); // '1' | '0' | null
        $sortBy   = $request->query('sortBy', 'users.id');
        $sortDir  = $request->query('sortDir', 'desc');
        $perPage  = (int) $request->query('perPage', 15);

        $allowedSorts = [
            'users.id', 'users.name', 'users.email', 'users.created_at',
            'profiles.firstname', 'profiles.lastname', 'profiles.country',
        ];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'users.id';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = User::role('student')
            ->select(['users.id', 'users.name', 'users.email', 'users.active', 'users.locale', 'users.created_at'])
            ->with([
                'profile:id,user_id,firstname,lastname,nickname,country,profile_image',
            ])
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('profiles.firstname', 'like', "%{$q}%")
                    ->orWhere('profiles.lastname', 'like', "%{$q}%")
                    ->orWhere('profiles.nickname', 'like', "%{$q}%");
            });
        }

        if ($country) {
            $query->where('profiles.country', $country);
        }

        if ($active !== null && in_array($active, ['0','1'], true)) {
            $query->where('users.active', (bool) $active);
        }

        $students = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Students/Index', [
            'students'  => $students,
            'courses'   => Course::select('id', 'title')->orderBy('title')->get(),
            'filters'   => [
                'q'       => $q,
                'country' => $country,
                'active'  => $active,
                'sortBy'  => $sortBy,
                'sortDir' => $sortDir,
                'perPage' => $perPage,
            ],
            // Para llenar selects de país si aplica en tu UI
            'countries' => Profile::query()
                ->whereNotNull('country')
                ->distinct()
                ->orderBy('country')
                ->pluck('country'),
        ]);
    }

    /**
     * Mostrar perfil de un estudiante.
     */
 
 

public function show(User $user): Response
{
    if (! $user->hasRole('student')) {
        abort(404);
    }

    // Carga perfil y cursos (para evitar N+1)
    $user->load(
        'profile' // relación via subscriptions
    );

    $enrollments = $user->courses->map(function ($course) {
        // por si en algún lado quitaran el alias, caemos a pivot
        $pivot = $course->subscription ?? $course->pivot;

        return [
            'id'          => $course->id,
            'title'       => $course->title,
            'image_cover' => $course->image_cover,
            'logo' => $course->logo,
            'description' => $course->description,
            'subscription' => [
                'id'         => $pivot->id        ?? null,
                'created_at' => $pivot->created_at ?? null,
                
 
                // agrega aquí otros campos del pivot si los incluyes en withPivot()
                // 'payment_status' => $pivot->payment_status ?? null,
            ],
        ];
    })->values();

    return Inertia::render('Admin/Students/Show', [
        'user'        => $user->only(['id', 'name', 'email', 'active', 'locale', 'created_at']),
        'profile'     => $user->profile,
        'enrollments' => $enrollments,
    ]);
}



    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create');
    }

    /**
     * Crear estudiante + profile en transacción.
     */
    public function store(Request $request)
    {
        $data = $this->validateStore($request);

        return DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'active'   => (bool) ($data['active'] ?? false),
                'locale'   => $request->input('locale', 'es'),
            ]);

            $user->assignRole('student');

            $user->profile()->create([
                'firstname'     => $data['firstname'],
                'lastname'      => $data['lastname'],
                'nickname'      => $this->guessNickname($user),
                'phone'         => $data['phone'] ?? null,
                'country'       => $data['country'] ?? null,
                'address'       => $data['address'] ?? null,
                'shirt_size'       => $data['shirt_size'] ?? null,
                'profile_image' => null,
                'cover_image'   => null,
                'bio'           => null,
                'activity' => $data['address'] ?? null,
                'experiencie' =>$data['address'] ?? null,
                'bussines_own' => $data['address'] ?? null,
                'bussines_name' => $data['address'] ?? null,
                'bussines_logo' => $data['address'] ?? null,
                'bussines_website' => $data['address'] ?? null,
                'bussines_category' => $data['address'] ?? null
            ]);

            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Estudiante creado exitosamente');
        });
    }

    /**
     * Edita por User (route model binding {user}).
     */
    public function edit(User $user): Response
    {
        if (! $user->hasRole('student')) {
            abort(404);
        }

        $user->load('profile');

        return Inertia::render('Admin/Students/Edit', [
            'user'    => $user->only(['id', 'name', 'email', 'active', 'locale']),
            'profile' => $user->profile,
        ]);
    }

    /**
     * Actualiza perfil (y opcionalmente datos básicos de User).
     * Soporta eliminación de imágenes con flags booleanos.
     */
    public function update(Request $request, User $user)
    {
        if (! $user->hasRole('student')) {
            abort(404);
        }

        $data = $this->validateUpdate($request, $user);

        return DB::transaction(function () use ($request, $user, $data) {
            // Opcional: actualizar algunos campos de User si se envían
            $user->fill([
                'name'   => $data['user_name']   ?? $user->name,
                'email'  => $data['user_email']  ?? $user->email,
                'active' => array_key_exists('user_active', $data) ? (bool) $data['user_active'] : $user->active,
                'locale' => $data['user_locale'] ?? $user->locale,
            ])->save();

            // Manejo de archivos
            $profilePayload = collect($data)
                ->only([
                    // Fiscales / personales / redes / description
                    'firstname',
                    'lastname',
                    'rfc',
                    'business_name',
                    'street',
                    'external_number',
                    'internal_number',
                    'state',
                    'municipality',
                    'neighborhood',
                    'postal_code',
                    'billing_email',
                    'tax_regime',
                    'cfdi_use',
                    'personal_email',
                    'shirt_size',
                    'country',
                    'whatsapp',
                    'nickname',
                    'website',
                    'facebook',
                    'instagram',
                    'tiktok',
                    'youtube',
                    'description',
                     'activity',
                    'experiencie',
                    'bussines_own',
                    'bussines_name',
                    
                    'bussines_website',
                    'bussines_category'
                    ])
                ->toArray();

            // Remover imágenes existentes si se pide
            if ($request->boolean('remove_profile_image') && $user->profile?->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
                $profilePayload['profile_image'] = null;
            }
            if ($request->boolean('remove_cover_image') && $user->profile?->cover_image) {
                Storage::disk('public')->delete($user->profile->cover_image);
                $profilePayload['cover_image'] = null;
            }

            // Nuevos uploads
            if ($request->hasFile('profile_image')) {
                if ($user->profile?->profile_image) {
                    Storage::disk('public')->delete($user->profile->profile_image);
                }
                $profilePayload['profile_image'] = $request->file('profile_image')
                    ->store('profiles/profile_images', 'public');
            }

            if ($request->hasFile('cover_image')) {
                if ($user->profile?->cover_image) {
                    Storage::disk('public')->delete($user->profile->cover_image);
                }
                $profilePayload['cover_image'] = $request->file('cover_image')
                    ->store('profiles/cover_images', 'public');
            }

             if ($request->hasFile('bussines_logo')) {
                if ($user->profile?->bussines_logo) {
                    Storage::disk('public')->delete($user->profile->bussines_logo);
                }
                $profilePayload['bussines_logo'] = $request->file('bussines_logo')
                    ->store('profiles/bussines_logos', 'public');
            }

            // Crear/actualizar perfil
            if ($user->profile) {
                $user->profile->update($profilePayload);
            } else {
                $user->profile()->create($profilePayload);
            }

            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Estudiante actualizado correctamente');
        });
    }

    /**
     * Elimina (soft delete si el modelo lo usa).
     */
    public function destroy(User $user)
    {
        if (! $user->hasRole('student')) {
            abort(404);
        }

        $user->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Estudiante eliminado exitosamente');
    }

    /**
     * Autocomplete: lista simple para selects (opcionalmente con ?q=).
     */
    public function list(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $students = User::role('student')
            ->select('users.id', 'users.name', 'users.email')
            ->with(['profile:id,user_id,firstname,lastname'])
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->where('users.name', 'like', "%{$q}%")
                        ->orWhere('users.email', 'like', "%{$q}%");
                });
            })
            ->orderBy('users.name')
            ->limit(20)
            ->get()
            ->map(function ($u) {
                $full = $u->name ?: trim(($u->profile->firstname ?? '') . ' ' . ($u->profile->lastname ?? ''));
                return [
                    'id'   => $u->id,
                    'name' => $full !== '' ? $full : $u->email,
                ];
            });

        return response()->json($students);
    }

    /**
     * Autocomplete por texto (para inputs tipo vue-select).
     */
    public function search(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $students = User::role('student')
            ->select('id', 'name', 'email')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn ($u) => [
                'label' => "{$u->name} ({$u->email})",
                'value' => $u->id,
            ]);

        return response()->json($students);
    }

    /**
     * Buscar por ID para setear valores iniciales en selects.
     */
    public function searchById(int $id)
    {
        $user = User::role('student')
            ->select('id', 'name', 'email')
            ->with(['profile:id,user_id'])
            ->findOrFail($id);

        return response()->json([
            'value' => $user->id,
            'label' => "{$user->name} ({$user->email})",
        ]);
    }

    /**
     * -------- VALIDACIONES --------
     */

    private function validateStore(Request $request): array
    {
        $messages = $this->validationMessages();

        return $request->validate([
            // User
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'active'   => ['nullable', 'boolean'],
            'locale'   => ['nullable', 'string', 'max:8'],

            // Profile mínimos
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:32'],
            'country'   => ['nullable', 'string', 'max:100'],
            'address'   => ['nullable', 'string', 'max:255'],
        ], $messages);
    }

    private function validateUpdate(Request $request, User $user): array
    {
        $messages = $this->validationMessages();

        return $request->validate([
            // Opcionalmente actualizar algunos campos de User
            'user_name'   => ['sometimes', 'required', 'string', 'max:255'],
            'user_email'  => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users','email')->ignore($user->id)],
            'user_active' => ['sometimes', 'boolean'],
            'user_locale' => ['sometimes', 'string', 'max:8'],

            // Fiscales
            'firstname'        => ['required', 'string', 'max:255'],
            'lastname'         => ['required', 'string', 'max:255'],
            'rfc'              => ['nullable', 'string', 'max:13'],
            'business_name'    => ['nullable', 'string', 'max:255'],
            'street'           => ['nullable', 'string', 'max:255'],
            'external_number'  => ['nullable', 'string', 'max:20'],
            'internal_number'  => ['nullable', 'string', 'max:20'],
            'state'            => ['nullable', 'string', 'max:100'],
            'municipality'     => ['nullable', 'string', 'max:100'],
            'neighborhood'     => ['nullable', 'string', 'max:100'],
            'postal_code'      => ['nullable', 'string', 'max:10'],
            'billing_email'    => ['nullable', 'email', 'max:255'],
            'tax_regime'       => ['nullable', 'string', 'max:100'],
            'cfdi_use'         => ['nullable', 'string', 'max:100'],

            // Personales
            'personal_email'   => ['required', 'email', 'max:255', Rule::unique('profiles','personal_email')->ignore(optional($user->profile)->id)],
            'country'          => ['required', 'string', 'max:100'],
            'whatsapp'         => ['nullable', 'string', 'max:20'],
            'nickname'         => ['required', 'string', 'max:50', Rule::unique('profiles','nickname')->ignore(optional($user->profile)->id)],

            // Archivos
            'profile_image'        => ['nullable', 'image', 'max:5120'],
            'cover_image'          => ['nullable', 'image', 'max:5120'],
            'remove_profile_image' => ['sometimes', 'boolean'],
            'remove_cover_image'   => ['sometimes', 'boolean'],

            // Redes sociales
            'website'   => ['nullable', 'url', 'max:255'],
            'facebook'  => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'tiktok'    => ['nullable', 'url', 'max:255'],
            'youtube'   => ['nullable', 'url', 'max:255'],

            'description' => ['nullable', 'string'],
            'activity' => ['nullable', 'string'],
            'experiencie' => ['nullable', 'string'],
            'bussines_own' => ['nullable', 'string'],
            'bussines_name' => ['nullable', 'string'],
            'bussines_logo' => ['nullable', 'image', 'max:5120'],
            'bussines_website' => ['nullable', 'string'],
            'bussines_category' => ['nullable', 'string']
        ], $messages);
    }

    /**
     * Utilidades
     */
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
            'country.required'    => 'El país es obligatorio.',
            'personal_email.required' => 'El correo personal es obligatorio.',
            'nickname.required'   => 'El nickname es obligatorio.',
        ];
    }
}
