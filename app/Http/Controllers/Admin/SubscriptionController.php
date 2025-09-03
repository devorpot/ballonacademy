<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class SubscriptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => Subscription::with(['user', 'course','currency'])->get(),
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
    $validated = $request->validate($this->validationRules($request));

    return DB::transaction(function () use ($validated) {
        $user     = User::findOrFail($validated['user_id']);
        $courseId = $validated['course_id'];

        // Crea si no existe (seguro ante carreras)
        $subscription = Subscription::firstOrCreate(
            ['user_id' => $validated['user_id'], 'course_id' => $courseId],
            $validated
        );

        // Asegura pivote sin duplicados
        $user->courses()->syncWithoutDetaching([$courseId]);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Suscripción creada exitosamente');
    });
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
    $validated = $request->validate($this->validationRules($request, $subscription));

    return DB::transaction(function () use ($validated, $subscription) {
        $user     = User::findOrFail($validated['user_id']);
        $courseId = $validated['course_id'];

        $subscription->update($validated);

        // Asegura pivote sin duplicados
        $user->courses()->syncWithoutDetaching([$courseId]);

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Suscripción actualizada correctamente');
    });
}

    public function destroy(Subscription $subscription)
{
    $user = $subscription->user;
    $courseId = $subscription->course_id;

    // Eliminar relación en la tabla pivote course_user
    $user->courses()->detach($courseId);

    // Eliminar la suscripción
    $subscription->delete();

    return redirect()->route('admin.subscriptions.index')
                     ->with('success', 'Suscripción eliminada exitosamente');
}


  public function show(Subscription $subscription)
{
    return Inertia::render('Admin/Subscriptions/Show', [
        'subscriptions' => Subscription::with(['user', 'course', 'currency'])->get(),
        'subscription' => $subscription->load(['user', 'course', 'currency']),
        'courses' => Course::all(),
    ]);
}

    
private function validationRules(Request $request, ?Subscription $ignore = null): array
{
    $uniqueCoursePerUser = Rule::unique('subscriptions', 'course_id')
        ->where(fn($q) => $q->where('user_id', $request->input('user_id')));

    if ($ignore) {
        $uniqueCoursePerUser = $uniqueCoursePerUser->ignore($ignore->id);
    }

    return [
        'user_id'            => ['required', 'exists:users,id'],
        'course_id'          => ['required', 'exists:courses,id', $uniqueCoursePerUser],
        'payment_amount'     => ['required','numeric','min:0'],
        'payment_currency'   => ['required','numeric','max:10'],
        'payment_description'=> ['nullable','string','max:2000'],
        'payment_type_id'    => ['required','exists:payment_types,id'],
        'payment_type'       => ['nullable','string','max:50'],
        'payment_card'       => ['nullable','string','max:50'],
        'payment_card_type'  => ['nullable','string','max:50'],
        'payment_card_brand' => ['nullable','string','max:50'],
        'payment_bank'       => ['nullable','string','max:100'],
        'payment_date'       => ['nullable','date'],
        'payment_refund_date'=> ['nullable','date'],
        'payment_refund_desc'=> ['nullable','string','max:2000'],
        'payment_status'     => ['nullable','string','max:50'],
        'payment_status_id'  => ['required','exists:payment_statuses,id'],
        'payment_stripe_id'  => ['required','string','max:50'],
        'payment_refund'     => ['nullable','boolean'],
        'used_coupon'        => ['nullable','boolean'],
        'coupon_id'          => ['required_if:used_coupon,true','nullable','string','max:255'],
        'coupon_discount'    => ['required_if:used_coupon,true','nullable','numeric','min:0'],
    ];
}
}
