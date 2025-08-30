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
                'sortDir' => $sortDir,
            ],
            'users' => User::role('student')->get(),
            'teachers' => Teacher::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Certificates/Create', [
            'users' => User::role('student')->get(),
            'teachers' => Teacher::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request, true);

        // Subidas iniciales
        $validated['photo']    = $this->handleUpload($request, 'photo', 'certificates/photos');
        $validated['logo']     = $this->handleUpload($request, 'logo',  'certificates/logos');
        $validated['pdf_path'] = $this->handleUpload($request, 'pdf',   'certificates/pdfs'); // nuevo campo

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
            'teachers' => Teacher::all(),
        ]);
    }

    public function update(Request $request, Certificate $certificate)
    {
        // En edición no obligamos archivos
        $validated = $this->validateData($request, false);

        $certificate->fill($validated);

        // Photo: reemplazo o borrado
        if ($request->hasFile('photo')) {
            $this->deleteFile($certificate->photo);
            $certificate->photo = $request->file('photo')->store('certificates/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteFile($certificate->photo);
            $certificate->photo = null;
        }

        // Logo: reemplazo o borrado
        if ($request->hasFile('logo')) {
            $this->deleteFile($certificate->logo);
            $certificate->logo = $request->file('logo')->store('certificates/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteFile($certificate->logo);
            $certificate->logo = null;
        }

        // PDF: reemplazo o borrado
        if ($request->hasFile('pdf')) {
            $this->deleteFile($certificate->pdf_path);
            $certificate->pdf_path = $request->file('pdf')->store('certificates/pdfs', 'public');
        } elseif ($request->boolean('remove_pdf')) {
            $this->deleteFile($certificate->pdf_path);
            $certificate->pdf_path = null;
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
        $this->deleteFile($certificate->pdf_path); // elimina PDF si existe

        $certificate->delete();

        return redirect()
            ->route('admin.certificates.index')
            ->with('success', 'Certificado eliminado exitosamente');
    }

    public function show(Certificate $certificate)
    {
        return Inertia::render('Admin/Certificates/Show', [
            'certificate' => $certificate->load(['user', 'master']),
        ]);
    }

    /**
     * $includeFiles=true: exige validación de archivos (crear).
     * $includeFiles=false: no exige reglas de archivo (editar) para permitir flags remove_* sin adjuntar archivo.
     */
    private function validateData(Request $request, bool $includeFiles = true): array
    {
        $rules = [
            'user_id'         => 'required|exists:users,id',
            'master_id'       => 'required|exists:teachers,id',
            'authorized_by'   => 'required|string|max:255',
            'date_start'      => 'nullable|date',
            'date_end'        => 'nullable|date',
            'date_expedition' => 'nullable|date',
            'comments'        => 'nullable|string|max:1000',

            // Flags de borrado (no obligatorios)
            'remove_photo'    => 'sometimes|boolean',
            'remove_logo'     => 'sometimes|boolean',
            'remove_pdf'      => 'sometimes|boolean', // nuevo flag
        ];

        if ($includeFiles) {
            $rules['photo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['logo']  = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            // 20 MB = 20480 KB
            $rules['pdf']   = 'nullable|file|mimes:pdf|max:20480';
        } else {
            // En edición permitimos subir archivos pero no son obligatorios
            $rules['photo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['logo']  = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['pdf']   = 'nullable|file|mimes:pdf|max:20480';
        }

        return $request->validate($rules);
    }

    private function handleUpload(Request $request, string $field, string $path): ?string
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function deleteFile(?string $filePath): void
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Opcional: descarga protegida del PDF si prefieres no exponer URL pública.
     * Asegúrate de registrar la ruta si la usas.
     *
     * Route::get('/admin/certificates/{certificate}/download', [CertificateController::class, 'download'])
     *     ->middleware(['auth', 'verified'])
     *     ->name('admin.certificates.download');
     */
    public function download(Certificate $certificate)
    {
        abort_unless($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path), 404);

        // Nombre sugerido; puedes personalizarlo
        $suggested = 'certificado-'.$certificate->id.'.pdf';

        return Storage::disk('public')->download($certificate->pdf_path, $suggested);
    }
}
