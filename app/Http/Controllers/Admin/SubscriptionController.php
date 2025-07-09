<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
public function index()
{
    return Inertia::render('Admin/Subscriptions/Index', [
        'subscriptions' => Subscription::with(['user', 'course'])->get(),
        'users' => User::whereHas('roles', function ($query) {
                        $query->where('name', 'student');
                    })
                    ->withCount('roles')
                    ->having('roles_count', 1)
                    ->get(),
        'courses' => Course::all(),
    ]);
}


public function create()
{
    return Inertia::render('Admin/Subscriptions/Create', [
        'users' => User::whereHas('roles', function ($query) {
                        $query->where('name', 'student');
                    })
                    ->withCount('roles')
                    ->having('roles_count', 1)
                    ->get(),
        'courses' => Course::all()
    ]);
}
  public function store(Request $request)
{
    $validated = $request->validate($this->validationRules());

    $user = User::findOrFail($validated['user_id']);
    $courseId = $validated['course_id'];

    // Verificar si ya está inscrito en el curso
    if ($user->courses()->where('courses.id', $courseId)->exists()) {
        return redirect()
            ->back()
            ->withErrors(['user_id' => 'El estudiante ya está inscrito en este curso.']);
    }

    // Crear suscripción
    $subscription = Subscription::create($validated);

    // Relacionar en tabla pivote
    $user->courses()->attach($courseId);

    return redirect()
        ->route('admin.subscriptions.index')
        ->with('success', 'Suscripción creada exitosamente');
}

public function edit(Subscription $subscription)
{
    $studentUsers = User::whereHas('roles', function ($query) {
                            $query->where('name', 'student');
                        })
                        ->withCount('roles')
                        ->having('roles_count', 1);

    return Inertia::render('Admin/Subscriptions/Edit', [
        'subscription' => $subscription->load(['user', 'course']),
        'users' => $studentUsers
                    ->orWhere('id', $subscription->user_id) // incluir el usuario actual aunque ya no cumpla filtro
                    ->get(),
        'courses' => Course::all()
    ]);
}

public function update(Request $request, Subscription $subscription)
{
    $validated = $request->validate($this->validationRules());

    $user = User::findOrFail($validated['user_id']);
    $courseId = $validated['course_id'];

    // Verifica si ya existe otra suscripción del mismo usuario al mismo curso
    $alreadySubscribed = Subscription::where('user_id', $validated['user_id'])
        ->where('course_id', $courseId)
        ->where('id', '!=', $subscription->id)
        ->exists();

    if ($alreadySubscribed) {
        return redirect()
            ->back()
            ->withErrors(['user_id' => 'Este estudiante ya está inscrito en este curso.']);
    }

    $subscription->update($validated);

    // Relación pivote, evita duplicados
    if (! $user->courses->contains($courseId)) {
        $user->courses()->attach($courseId);
    }

    return redirect()
        ->route('admin.subscriptions.index')
        ->with('success', 'Suscripción actualizada correctamente');
}

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Suscripción eliminada exitosamente');
    }

    public function show(Subscription $subscription)
    {
        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription->load(['user', 'course'])
        ]);
    }

    /**
     * Devuelve las reglas de validación para store y update.
     *
     * @return array
     */
    private function validationRules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_amount' => 'required|numeric|min:0',
            'payment_currency' => 'required|string|max:10',
            'payment_description' => 'nullable|string|max:2000',
            'payment_type' => 'nullable|string|max:50',
            'payment_card' => 'nullable|string|max:50',
            'payment_card_type' => 'nullable|string|max:50',
            'payment_card_brand' => 'nullable|string|max:50',
            'payment_bank' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'payment_refund_date' => 'nullable|date',
            'payment_refund_desc' => 'nullable|string|max:2000',
            'payment_status' => 'required|string|max:50',
        ];
    }
}
