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
            'users' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))
                            ->withCount('roles')
                            ->having('roles_count', 1)
                            ->get(),
            'courses' => Course::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Subscriptions/Create', [
            'users' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))
                            ->withCount('roles')
                            ->having('roles_count', 1)
                            ->get(),
            'courses' => Course::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $user = User::findOrFail($validated['user_id']);
        $courseId = $validated['course_id'];

        // Verificar si ya existe suscripción
        if ($user->courses()->where('courses.id', $courseId)->exists()) {
            return back()->withErrors(['user_id' => 'El estudiante ya está inscrito en este curso.']);
        }

        // Crear suscripción y relacionar con curso
        Subscription::create($validated);
        $user->courses()->attach($courseId);

        return redirect()->route('admin.subscriptions.index')
                         ->with('success', 'Suscripción creada exitosamente');
    }

    public function edit(Subscription $subscription)
    {
        $studentUsers = User::whereHas('roles', fn ($q) => $q->where('name', 'student'))
                            ->withCount('roles')
                            ->having('roles_count', 1)
                            ->orWhere('id', $subscription->user_id) // incluir al usuario actual
                            ->get();

        return Inertia::render('Admin/Subscriptions/Edit', [
            'subscription' => $subscription->load(['user', 'course']),
            'users' => $studentUsers,
            'courses' => Course::all(),
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate($this->validationRules());

        $user = User::findOrFail($validated['user_id']);
        $courseId = $validated['course_id'];

        // Verificar que no haya otra suscripción duplicada
        $duplicate = Subscription::where('user_id', $validated['user_id'])
                                ->where('course_id', $courseId)
                                ->where('id', '!=', $subscription->id)
                                ->exists();

        if ($duplicate) {
            return back()->withErrors(['user_id' => 'Este estudiante ya está inscrito en este curso.']);
        }

        $subscription->update($validated);

        // Asegura relación pivote sin duplicados
        if (! $user->courses->contains($courseId)) {
            $user->courses()->attach($courseId);
        }

        return redirect()->route('admin.subscriptions.index')
                         ->with('success', 'Suscripción actualizada correctamente');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
                         ->with('success', 'Suscripción eliminada exitosamente');
    }

    public function show(Subscription $subscription)
    {
        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription->load(['user', 'course']),
        ]);
    }

    private function validationRules(): array
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
            'payment_stripe_id' => 'required|string|max:50',
            'payment_refund' => 'nullable|boolean',
            'used_coupon' => 'nullable|boolean',
            'coupon_id' => 'required_if:used_coupon,true|nullable|string|max:255',
            'coupon_discount' => 'required_if:used_coupon,true|nullable|numeric|min:0',
        ];
    }
}
