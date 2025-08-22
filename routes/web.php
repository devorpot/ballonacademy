<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
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
use App\Http\Controllers\Admin\VideoResourcesController; 
use App\Http\Controllers\Admin\VideoMaterialController;
use App\Http\Controllers\Admin\VideoCaptionsController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonVideosController;
use App\Http\Controllers\Admin\LessonEvaluationsController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PaymentStatusController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\DistributorController;
use App\Http\Controllers\Admin\EvaluationQuestionController;
use App\Http\Controllers\Admin\EvaluationUsersController;
use App\Http\Controllers\Admin\ActivationsController;
use App\Http\Controllers\PublicRegistrationController;


use App\Http\Controllers\Frontend\DashboardController as FrontendDashboardController;
use App\Http\Controllers\Frontend\CoursesController as FrontendCoursesController;
use App\Http\Controllers\Frontend\VideoController as FrontendVideoController;
use App\Http\Controllers\Frontend\ProfileController as FrontendProfileController;

use App\Http\Controllers\Frontend\EvaluationController as FrontendEvaluationController;

use App\Http\Controllers\Frontend\EvaluationUsersController as FrontendEvaluationUsersController;
use App\Http\Controllers\Frontend\VideosResourcesController as FrontendVideosResourcesController;
use App\Http\Controllers\Frontend\SecurityController  as FrontendSecurityController;
use App\Http\Controllers\Frontend\DistributorController  as FrontendDistributorController;

use App\Http\Controllers\Frontend\LessonsController  as FrontendLessonsController;
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


// Recuperar contraseña
Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('/activate/{user}', [UserController::class, 'activate'])
    ->name('users.activate')
    ->middleware('signed'); // exige firma válida

// Ruta pública para usar el hash
Route::get('/register/student/{hash}', [PublicRegistrationController::class, 'show'])
    ->name('public.register.student');

Route::post('/register/student/{hash}', [PublicRegistrationController::class, 'store'])
    ->name('public.register.student.store');

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

   // Adjuntar videos a una lección
   Route::post('/lessons/{lesson}/videos', [LessonVideosController::class, 'attach'])
        ->name('lessons.videos.attach');

    // Quitar video de una lección (opcional, para el botón Eliminar)
    Route::delete('lessons/{lesson}/videos/{video}', [LessonVideosController::class, 'detach'])
        ->name('lessons.videos.detach');

    Route::post('lessons/{lesson}/videos/reorder', [LessonVideosController::class, 'reorder'])
        ->name('lessons.videos.reorder');

/*Evaluations*/
       
   Route::post('/lessons/{lesson}/evaluations', [LessonEvaluationsController::class, 'attach'])
        ->name('lessons.evaluations.attach');

    // Quitar video de una lección (opcional, para el botón Eliminar)
    Route::delete('lessons/{lesson}/evaluations/{evaluation}', [LessonEvaluationsController::class, 'detach'])
        ->name('lessons.evaluations.detach');

    Route::post('lessons/{lesson}/evaluations/reorder', [LessonEvaluationsController::class, 'reorder'])
        ->name('lessons.evaluations.reorder');

Route::resource('lessons', LessonController::class);



Route::get('courses/{course}/lessons-panel', [LessonController::class, 'lessonsPanel'])
    ->name('courses.lessons.panel');

Route::get('courses/{course}/lessons/list', [LessonController::class, 'list'])
    ->name('courses.lessons.list');

Route::post('courses/{course}/lessons/reorder', [LessonController::class, 'reorderLessons'])
    ->name('courses.lessons.reorder');

Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'deleteLesson'])
    ->name('courses.lessons.delete');


 

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


 Route::get('/videos/by-course/{course}', [VideoController::class, 'getByCourse'])
        ->name('videos.by-course');

  Route::resource('videos.resources', VideoResourcesController::class)
             ->parameters(['videos' => 'video']); // /admin/videos/{video}/resources/{resource}

    Route::prefix('videos/{video}')
            ->name('videos.')
            ->scopeBindings() // opcional pero recomendable para anidadas
            ->group(function () {
                Route::get('captions',                    [VideoCaptionsController::class, 'index'])->name('captions.index');
                Route::post('captions',                   [VideoCaptionsController::class, 'store'])->name('captions.store');
                Route::get('captions/{caption}',          [VideoCaptionsController::class, 'show'])->name('captions.show');
                Route::put('captions/{caption}',          [VideoCaptionsController::class, 'update'])->name('captions.update');
                Route::delete('captions/{caption}',       [VideoCaptionsController::class, 'destroy'])->name('captions.destroy');
            });




    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');

    Route::post('/users/{user}/activation/send', [UserController::class, 'sendActivation'])
        ->name('users.activation.send');

    //Estudiantes



    Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
    Route::resource('teachers', TeacherController::class);

    Route::resource('certificates', CertificateController::class);
    Route::resource('distributors', DistributorController::class);
    Route::resource('subscriptions', SubscriptionController::class);

    
  


  Route::resource('students', StudentController::class)->except(['create','edit','show'])
        ->parameters(['students' => 'user']);
    Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
    Route::get('students/{user}', [StudentController::class, 'show'])->name('students.show');
    Route::get('students/{user}/edit', [StudentController::class, 'edit'])->name('students.edit');
    // Autocomplete
    Route::get('students-list', [StudentController::class, 'list'])->name('students.list');
    Route::get('students-search', [StudentController::class, 'search'])->name('students.search');
    Route::get('students-search/{id}', [StudentController::class, 'searchById'])->name('students.searchById');

 

    //Currency

    Route::get('options/currencies', [CurrencyController::class, 'options']);
    Route::get('options/payment_type', [PaymentTypeController::class, 'options']);
    Route::get('options/payment_status', [PaymentStatusController::class, 'options']);

    
    Route::get('/evaluations/videos/combo/{course}', [EvaluationController::class, 'videosByCourse'])->name('evaluations.videos.combo');

    Route::get('/evaluations/lessons/combo/{course}', [EvaluationController::class, 'lessonsByCourse'])->name('evaluations.lessons.combo');
    

     Route::get('/evaluations/by-course/{course}', [EvaluationController::class, 'getByCourse'])->name('evaluations.by-course');

    Route::resource('evaluations', EvaluationController::class);


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


     Route::get('evaluation-users/course/{course}/index', [EvaluationUsersController::class, 'byCourse'])
        ->name('evaluation-users.course.index');

    Route::resource('evaluation-users', EvaluationUsersController::class)
        ->only(['index', 'show', 'edit', 'update', 'destroy']);
        
     Route::put('evaluation-users/{evaluation_user}/status', [EvaluationUsersController::class, 'updateStatus'])->name('evaluation-users.update-status');



Route::post('/activations', [ActivationsController::class, 'store'])
            ->name('activations.store');


 




});
 
// ----------------------------------
// Panel del estudiante (solo student)
// ----------------------------------



Route::middleware(['auth', 'role:student'])
    ->prefix('frontend')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/', [FrontendDashboardController::class, 'index'])->name('index');
        
    Route::get('/distributors', [FrontendDistributorController::class, 'index'])->name('distributors.index');


   Route::get('/courses/{course}/lessons', [FrontendLessonsController::class, 'index'])
            ->whereNumber('course')
            ->name('courses.lessons.index');

   Route::get('/courses/{course}/lessons/{lesson}', [FrontendLessonsController::class, 'show'])
            ->whereNumber('course')
            ->whereNumber('lesson')
            ->name('courses.lessons.show');

  Route::get('/courses/{course}/lessons/{lesson}/videos/{video}',
            [\App\Http\Controllers\Frontend\LessonsController::class, 'showVideo']
        )
        ->whereNumber(['course','lesson','video'])
        ->name('courses.lessons.videos.show');



     Route::get('/security', [FrontendSecurityController::class, 'edit'])->name('security.edit');
     Route::put('/security', [FrontendSecurityController::class, 'update'])->name('security.update');

        Route::post('/user/update-locale', [UserController::class, 'updateLocale'])->name('user.update-locale');

        
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

    Route::get('courses/{course}/evaluations/{evaluation}/show', [FrontendEvaluationController::class, 'show'])
    ->name('courses.evaluations.show');

    Route::get('courses/{course}/evaluations/{evaluation}/preview', [FrontendEvaluationUsersController::class, 'preview'])
        ->name('courses.evaluations.evaluation.preview');

    Route::get('/courses/{course}/evaluations/{evaluation}/download', [FrontendEvaluationController::class, 'download'])
    ->name('courses.evaluations.download');

        Route::get('/evaluations/{evaluation}/thank-you', [FrontendEvaluationsController::class, 'thankyou'])
            ->name('evaluations.thankyou');



Route::delete('/evaluation-users/by-evaluation', [FrontendEvaluationUsersController::class, 'destroyByEvaluation'])
    ->name('evaluation-users.destroy-by-evaluation');


Route::resource('evaluation-users', FrontendEvaluationUsersController::class)->only(['index', 'store', 'show']);



Route::delete('/evaluation-users/by-evaluation', [FrontendEvaluationUsersController::class, 'destroyByEvaluation'])
    ->name('evaluation-users.destroy-by-evaluation');


  Route::get('/videos/{video}/resources', [FrontendVideosResourcesController::class, 'index'])->name('videos.resources.index');

  Route::get('/videos/{video}/resources/{resource}', [FrontendVideosResourcesController::class, 'show'])
            ->name('videos.resources.show');




Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => 'required|in:es,en']);
    $locale = $request->input('locale');

    if ($request->user()) {
        $request->user()->update(['locale' => $locale]);
    }
    $request->session()->put('locale', $locale);

    return back();
})->middleware(['auth'])->name('locale.update');


    });