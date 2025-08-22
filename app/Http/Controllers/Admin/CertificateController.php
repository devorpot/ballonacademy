<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'date_expedition', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $certificates = Certificate::with(['user', 'master'])
            ->orderBy($sortBy, $sortDir)
            ->get();

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir
            ],
            'users' => User::role('student')->get(),
            'teachers' => Teacher::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Certificates/Create', [
            'users' => User::role('student')->get(),
            'teachers' => Teacher::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        // Carga inicial de archivos (cuando se suben en creación)
        $validated['photo'] = $this->handleUpload($request, 'photo', 'certificates/photos');
        $validated['logo']  = $this->handleUpload($request, 'logo', 'certificates/logos');

        Certificate::create($validated);

        return redirect()
            ->route('admin.certificates.index')
            ->with('success', 'Certificado creado exitosamente');
    }

    public function edit(Certificate $certificate)
    {
        return Inertia::render('Admin/Certificates/Edit', [
            'certificate' => $certificate->load(['user', 'master']),
            'users' => User::role('student')->get(),
            'teachers' => Teacher::all()
        ]);
    }

    public function update(Request $request, Certificate $certificate)
    {
        // Al editar, no exigimos reglas de archivo si no vienen; igual que en CourseController
        $validated = $this->validateData($request, false);

        $certificate->fill($validated);

        // Photo: soporta reemplazo o borrado con flag remove_photo
        if ($request->hasFile('photo')) {
            $this->deleteFile($certificate->photo);
            $certificate->photo = $request->file('photo')->store('certificates/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteFile($certificate->photo);
            $certificate->photo = null;
        }

        // Logo: soporta reemplazo o borrado con flag remove_logo
        if ($request->hasFile('logo')) {
            $this->deleteFile($certificate->logo);
            $certificate->logo = $request->file('logo')->store('certificates/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteFile($certificate->logo);
            $certificate->logo = null;
        }

        $certificate->save();

        return redirect()
            ->route('admin.certificates.index')
            ->with('success', 'Certificado actualizado correctamente');
    }

    public function destroy(Certificate $certificate)
    {
        $this->deleteFile($certificate->photo);
        $this->deleteFile($certificate->logo);

        $certificate->delete();

        return redirect()
            ->route('admin.certificates.index')
            ->with('success', 'Certificado eliminado exitosamente');
    }

    public function show(Certificate $certificate)
    {
        return Inertia::render('Admin/Certificates/Show', [
            'certificate' => $certificate->load(['user', 'master'])
        ]);
    }

    /**
     * $includeFiles=true: exige validación de archivos (crear).
     * $includeFiles=false: no exige reglas de archivo (editar) para permitir flags remove_* sin adjuntar archivo.
     */
    private function validateData(Request $request, $includeFiles = true)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'master_id' => 'required|exists:teachers,id',
            'authorized_by' => 'required|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
            'date_expedition' => 'nullable|date',
            'comments' => 'nullable|string|max:1000',

            // Flags de borrado desde el formulario de edición (no obligatorios)
            'remove_photo' => 'sometimes|boolean',
            'remove_logo'  => 'sometimes|boolean',
        ];

        if ($includeFiles) {
            $rules['photo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['logo']  = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $request->validate($rules);
    }

    private function handleUpload(Request $request, $field, $path)
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function deleteFile($filePath)
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Aún disponible por si quieres usarla en otros contextos.
     * En update() ya seguimos el patrón de flags como en cursos.
     */
    private function updateFile(Request $request, $currentPath, $field, $path)
    {
        if ($request->hasFile($field)) {
            $this->deleteFile($currentPath);
            return $this->handleUpload($request, $field, $path);
        }
        return $currentPath;
    }
}
