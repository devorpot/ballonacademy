<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\VideoController;

use App\Http\Controllers\Admin\CurrencyController;

use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\CoursesController;

// ----------------------------------
// Autenticación
// ----------------------------------

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);
Route::get('/login', [AuthController::class, 'loginForm']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ----------------------------------
// Panel de administración (solo admin)
// ----------------------------------

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/', [AdministratorController::class, 'index'])->name('dashboard');

    // Cursos
    Route::resource('courses', CourseController::class);
    Route::get('courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
    Route::post('courses/{course}/assign-students', [CourseController::class, 'assignStudents'])->name('courses.assign-students');

    // Videos por curso
 
    Route::get('courses/{course}/videos-panel', [VideoController::class, 'panel'])->name('courses.videos.panel');
    Route::get('courses/{course}/videos/videos-list', [VideoController::class, 'list'])->name('courses.videos.list');
    Route::post('courses/{course}/videos/reorder', [VideoController::class, 'reorderVideos'])->name('courses.videos.reorder');


    Route::resource('courses.videos', VideoController::class);

    // Otros recursos
    Route::resource('videos', VideoController::class);


    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');

    //Estudiantes

    Route::get('students/list', [StudentController::class, 'list'])->name('students.list');
    Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
    Route::get('students/search/{id}', [StudentController::class, 'searchById'])->name('students.searchById');

    Route::resource('students', StudentController::class);


    Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
    Route::resource('teachers', TeacherController::class);

    Route::resource('certificates', CertificateController::class);
    Route::resource('subscriptions', SubscriptionController::class);

    Route::get('/students/{user}/profile', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('/students/{user}/profile/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/students/{user}/profile', [ProfileController::class, 'update'])->name('profiles.update');
    Route::delete('/students/{user}/profile', [ProfileController::class, 'destroy'])->name('profiles.destroy');


    //Currency

    Route::get('options/currencies', [CurrencyController::class, 'options']);


});

// ----------------------------------
// Panel del estudiante (solo student)
// ----------------------------------

Route::middleware(['auth', 'role:student'])
    ->prefix('frontend')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', [CoursesController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/videos/{video}', [CoursesController::class, 'videoDetail'])->name('courses.videos.show');

        // Ruta para servir videos protegidos
        Route::get('/videos/secure/{filename}', function ($filename) {
            $user = auth()->user();
            $video = \App\Models\Video::where('video_url', $filename)->firstOrFail();

            // Verifica si el estudiante está inscrito en el curso
            if (!$user->courses->contains($video->course_id)) {
                abort(403);
            }

            $path = storage_path("app/videos/{$filename}");

            if (!file_exists($path)) {
                abort(404);
            }

            return response()->file($path);
        })->name('videos.secure');
    });
