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
        'users' => User::role('student')->get(),
        'courses' => Course::all(),
    ]);
}

    public function create()
    {
        return Inertia::render('Admin/Subscriptions/Create', [
            'users' => User::role('student')->get(),
            'courses' => Course::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        Subscription::create($validated);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Suscripción creada exitosamente');
    }

    public function edit(Subscription $subscription)
    {
        return Inertia::render('Admin/Subscriptions/Edit', [
            'subscription' => $subscription->load(['user', 'course']),
            'users' => User::role('student')->get(),
            'courses' => Course::all()
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate($this->validationRules());

        $subscription->update($validated);

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
