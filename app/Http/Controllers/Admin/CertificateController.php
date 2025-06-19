<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
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
        'users' => User::role('student')->get(),  // Solo usuarios con rol student
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
            'photo' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
        ]);

        Certificate::create($validated);

        return redirect()->route('admin.certificates.index')->with('success', 'Certificado creado exitosamente');
    }

    public function edit(Certificate $certificate)
{
    return Inertia::render('Admin/Certificates/Edit', [
        'certificate' => $certificate->load(['user', 'master']),
        'users' => User::role('student')->get(),  // Solo usuarios con rol student
        'teachers' => Teacher::all()
    ]);
}

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'master_id' => 'required|exists:teachers,id',
            'authorized_by' => 'required|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
            'date_expedition' => 'nullable|date',
            'comments' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
        ]);

        $certificate->update($validated);

        return redirect()->route('admin.certificates.index')->with('success', 'Certificado actualizado correctamente');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('admin.certificates.index')->with('success', 'Certificado eliminado exitosamente');
    }

    public function show(Certificate $certificate)
    {
        return Inertia::render('Admin/Certificates/Show', [
            'certificate' => $certificate->load(['user', 'master'])
        ]);
    }
}
