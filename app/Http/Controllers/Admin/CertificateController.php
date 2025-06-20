<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
 
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['user', 'master'])->get();

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => $certificates
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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'master_id' => 'required|exists:teachers,id',
            'authorized_by' => 'required|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
            'date_expedition' => 'nullable|date',
            'comments' => 'nullable|string|max:1000',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        // Procesar foto
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('certificates/photos', 'public');
        }

        // Procesar logo
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('certificates/logos', 'public');
        }

        // Guardar el certificado
        Certificate::create($validated);

        return redirect()->route('admin.certificates.index')
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
    // Validar los campos normales
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'master_id' => 'required|exists:teachers,id',
        'authorized_by' => 'required|string|max:255',
        'date_start' => 'nullable|date',
        'date_end' => 'nullable|date',
        'date_expedition' => 'nullable|date',
        'comments' => 'nullable|string|max:1000',
    ]);

    // Llenar los campos validados
    $certificate->fill($validated);

    // Procesar logo
    if ($request->hasFile('logo')) {
        $request->validate([
            'logo' => 'file|mimes:jpeg,png,jpg,gif|max:10000'
        ]);

        if ($certificate->logo) {
            Storage::disk('public')->delete($certificate->logo);
        }

        $path = Storage::disk('public')->putFile('certificates/logos', $request->file('logo'), [
            'visibility' => null // Evita error de setVisibility
        ]);

        if ($path) {
            $certificate->logo = $path;
        }
    }

    // Procesar photo
    if ($request->hasFile('photo')) {
        $request->validate([
            'photo' => 'file|mimes:jpeg,png,jpg,gif|max:10000'
        ]);

        if ($certificate->photo) {
            Storage::disk('public')->delete($certificate->photo);
        }

        $path = Storage::disk('public')->putFile('certificates/photos', $request->file('photo'), [
            'visibility' => null // Evita error de setVisibility
        ]);

        if ($path) {
            $certificate->photo = $path;
        }
    }

    // Guardar todo en un solo paso
    $certificate->save();

    return redirect()->route('admin.certificates.index')
        ->with('success', 'Certificado actualizado correctamente');
}



    public function destroy(Certificate $certificate)
    {
        // Borrar archivos si existen
        if ($certificate->photo) {
            Storage::disk('public')->delete($certificate->photo);
        }
        if ($certificate->logo) {
            Storage::disk('public')->delete($certificate->logo);
        }

        $certificate->delete();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificado eliminado exitosamente');
    }

    public function show(Certificate $certificate)
    {
        return Inertia::render('Admin/Certificates/Show', [
            'certificate' => $certificate->load(['user', 'master'])
        ]);
    }
}
