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
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PaymentStatusController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\VideoMaterialController;
use App\Http\Controllers\Admin\EvaluationQuestionController;

use App\Http\Controllers\Frontend\DashboardController as FrontendDashboardController;
use App\Http\Controllers\Frontend\CoursesController as FrontendCoursesController;
use App\Http\Controllers\Frontend\VideoController as FrontendVideoController;
use App\Http\Controllers\Frontend\ProfileController as FrontendProfileController;

use App\Http\Controllers\Frontend\EvaluationController as FrontendEvaluationController;

use App\Http\Controllers\Api\VideoActivityController;
use App\Http\Controllers\Api\ActivityController;
 
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
 
    Route::get('courses/{course}/videos-panel', [VideoController::class, 'videosPanel'])->name('courses.videos.panel');

    Route::get('courses/{course}/videos/videos-list', [VideoController::class, 'list'])->name('courses.videos.list');

    Route::post('courses/{course}/videos/reorder', [VideoController::class, 'reorderVideos'])->name('courses.videos.reorder');

    Route::delete('courses/{course}/videos/{video}', [VideoController::class, 'deleteVideo'])->name('courses.videos.delete');



    //Route::resource('courses.videos', VideoController::class);

    // Otros recursos
    Route::get('videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');

    Route::resource('videos', VideoController::class);





// Listar materiales de un video (usada por fetchMaterials)
Route::get('/videos/{video}/materials', [VideoMaterialController::class, 'index'])
    ->name('videos.materials.index');

// Guardar uno o varios materiales (usada por addMaterial)
Route::post('/videos/{video}/materials', [VideoMaterialController::class, 'store'])
    ->name('videos.materials.store');

// Eliminar material individual (usada por removeMaterial)
Route::delete('/videos/{video}/materials/{material}', [VideoMaterialController::class, 'destroy'])
    ->name('videos.materials.destroy');

 Route::put('/videos/{video}/materials/{material}', [VideoMaterialController::class, 'update'])->name('videos.materials.update');


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


     
    Route::resource('evaluations', EvaluationController::class);

  

    Route::get('/students/{user}/profile', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('/students/{user}/profile/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/students/{user}/profile', [ProfileController::class, 'update'])->name('profiles.update');
    Route::delete('/students/{user}/profile', [ProfileController::class, 'destroy'])->name('profiles.destroy');


    //Currency

    Route::get('options/currencies', [CurrencyController::class, 'options']);
    Route::get('options/payment_type', [PaymentTypeController::class, 'options']);
    Route::get('options/payment_status', [PaymentStatusController::class, 'options']);

    //Evaluations



   


    Route::prefix('evaluations/{evaluation}/questions')->name('evaluation-questions.')->group(function () {
        Route::get('/', [EvaluationQuestionController::class, 'index'])->name('index');
        Route::get('create', [EvaluationQuestionController::class, 'create'])->name('create');
        Route::post('/', [EvaluationQuestionController::class, 'store'])->name('store');
        Route::get('{question}/edit', [EvaluationQuestionController::class, 'edit'])->name('edit');
        Route::put('{question}', [EvaluationQuestionController::class, 'update'])->name('update');
        Route::delete('{question}', [EvaluationQuestionController::class, 'destroy'])->name('destroy');
        Route::post('reorder', [EvaluationQuestionController::class, 'reorder'])->name('reorder');
        Route::get('preview', [EvaluationQuestionController::class, 'preview'])->name('preview');


    });


});
 
// ----------------------------------
// Panel del estudiante (solo student)
// ----------------------------------


Route::middleware(['auth', 'role:student'])
    ->prefix('frontend')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/', [FrontendDashboardController::class, 'index'])->name('index');
        
        Route::get('/courses', [FrontendCoursesController::class, 'index'])->name('courses.index');

        Route::get('/courses/{id}', [FrontendCoursesController::class, 'show'])->name('courses.show');
        
        Route::get('/courses/{course}/videos/{video}', [FrontendCoursesController::class, 'videoDetail'])->name('courses.videos.show');





        Route::get('/videos/{course}/video/{video}', [FrontendVideoController::class, 'show'])->name('videos.video.show');

        // Ruta para servir videos protegidos
        Route::get('/videos/secure/{filename}', [FrontendCoursesController::class, 'streamProtectedVideo'])
            ->name('videos.secure');

        // Perfil del estudiante
        Route::get('/profile', [FrontendProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [FrontendProfileController::class, 'update'])->name('profile.update');


       Route::post('/video-activity', [VideoActivityController::class, 'store'])->name('video.activity');


       Route::get('/videos/{course}/list', [FrontendVideoController::class, 'listCourseVideos'])->name('videos.list');


 


 

        Route::post('/courses/course-activity', [ActivityController::class, 'courseEnded'])
            ->name('courses.activity');


        Route::post('/courses/video-activity', [ActivityController::class, 'videoEnded'])
            ->name('video.activity');


      Route::get('/courses/{course}/evaluations', [FrontendEvaluationController::class, 'index'])
    ->name('courses.evaluations.index');

Route::post('/courses/{course}/evaluations', [FrontendEvaluationController::class, 'store'])
    ->name('courses.evaluations.store');

Route::get('/courses/{course}/evaluations/create', [FrontendEvaluationController::class, 'create'])
    ->name('courses.evaluations.create');

Route::get('/courses/{course}/evaluations/{evaluation}/edit', [FrontendEvaluationController::class, 'edit'])
    ->name('courses.evaluations.edit');

Route::put('/courses/{course}/evaluations/{evaluation}', [FrontendEvaluationController::class, 'update'])
    ->name('courses.evaluations.update');

Route::get('/courses/{course}/evaluations/{evaluation}', [FrontendEvaluationController::class, 'show'])
    ->name('courses.evaluations.show');

Route::delete('/courses/{course}/evaluations/{evaluation}', [FrontendEvaluationController::class, 'destroy'])
    ->name('courses.evaluations.destroy');

Route::get('/courses/{course}/evaluations/{evaluation}/download', [FrontendEvaluationController::class, 'download'])
    ->name('courses.evaluations.download');






    });