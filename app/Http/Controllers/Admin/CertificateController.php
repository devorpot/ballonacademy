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

        $validated['photo'] = $this->handleUpload($request, 'photo', 'certificates/photos');
        $validated['logo'] = $this->handleUpload($request, 'logo', 'certificates/logos');

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
        $validated = $this->validateData($request, false);

        $certificate->fill($validated);

        $certificate->photo = $this->updateFile($request, $certificate->photo, 'photo', 'certificates/photos');
        $certificate->logo = $this->updateFile($request, $certificate->logo, 'logo', 'certificates/logos');

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
        ];

        if ($includeFiles) {
            $rules['photo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules['logo'] = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
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

    private function updateFile(Request $request, $currentPath, $field, $path)
    {
        if ($request->hasFile($field)) {
            $this->deleteFile($currentPath);
            return $this->handleUpload($request, $field, $path);
        }
        return $currentPath;
    }
}
