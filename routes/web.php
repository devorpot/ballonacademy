<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;

// ----------------------------------
// Autenticación
// ----------------------------------
Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::get('/login', [AuthController::class, 'loginForm']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

// ----------------------------------
// Dashboard redireccionado según rol
// ----------------------------------
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    return Inertia::render('Auth/Dashboard');
})->middleware('auth')->name('dashboard');

// ----------------------------------
// Panel de administración (solo admin)
// ----------------------------------
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => Inertia::render('Admin/Index'))->name('dashboard');

        // Usuarios
        Route::resource('users', UserController::class);
        Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])
            ->name('users.assign-roles');

        // Alumnos
        Route::resource('students', StudentController::class);
        // Maestros
        Route::resource('teachers', TeacherController::class)->only(['index']);

        // Certificados
        Route::resource('certificates', CertificateController::class)->only(['index']);

        // Cursos
        Route::resource('courses', CourseController::class);
        Route::resource('courses.videos', VideoController::class);

        // Suscripciones
        Route::resource('subscriptions', SubscriptionController::class)->only(['index']);

        // Pagos
        Route::resource('payments', PaymentController::class)->only(['index']);
    });
