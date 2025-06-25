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
 
use App\Http\Controllers\Admin\VideoController;
 

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
        Route::get('students/list', [StudentController::class, 'list'])->name('students.list');
        Route::resource('students', StudentController::class);
        // Maestros

        Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
        Route::resource('teachers', TeacherController::class);
        




        // Certificados
        Route::resource('certificates', CertificateController::class);
  

        // Cursos
        Route::post('courses/{course}/assign-students', [CourseController::class, 'assignStudents'])
    ->name('courses.assign-students');
        Route::resource('courses', CourseController::class);
        Route::resource('courses.videos', VideoController::class);

       Route::post('/courses/{course}/videos/reorder', [VideoController::class, 'reorderVideos'])
    ->name('courses.videos.reorder');
        // Suscripciones
        Route::resource('subscriptions', SubscriptionController::class);
 
    });


 