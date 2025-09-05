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
use App\Http\Controllers\Admin\EvaluationFileController;
use App\Http\Controllers\Admin\EvaluationUsersController;
use App\Http\Controllers\Admin\ActivationsController;
use App\Http\Controllers\Admin\ExtraClassController;
use App\Http\Controllers\Admin\VideoCommentsController;
use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
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
use App\Http\Controllers\Frontend\VideoCommentsController  as FrontendVideoCommentsController;
use App\Http\Controllers\Frontend\ExtraClassController as FrontendExtraClassController;
use App\Http\Controllers\Frontend\CourseVideosController as FrontendCourseVideosController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;



use App\Http\Controllers\Api\VideoActivityController;
use App\Http\Controllers\Api\ActivityController;
 
// ----------------------------------
// Autenticación
// ----------------------------------

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/', [AuthController::class, 'login']);



/*
|--------------------------------------------------------------------------
| Rutas públicas para invitados (guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login (formulario y envío)
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    // Olvidé mi contraseña: formulario para pedir el email
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])
        ->name('password.request');

    // Envío del email con el enlace de reseteo
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    // Formulario de nueva contraseña (la notificación usa  
        
        Route::get('/reset-password/{token}', function (Request $request, string $token) {
        // Retorna tu vista/Inertia para el form de cambio de password
        // Debes pasar token y email (si lo usas en la vista)
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    })->name('password.reset');


    // Guardar nueva contraseña (la notificación envía a este POST)
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Cambiar contraseña (usuario autenticado)
    Route::get('/change-password', [AuthController::class, 'changePasswordForm'])
        ->name('password.change.form');

    Route::post('/change-password', [AuthController::class, 'changePassword'])
        ->name('password.change');

    // Generar token de activación (si solo personal interno debe usarlo, mantenlo protegido)
    Route::get('/activation-token', [AuthController::class, 'activationTokenForm'])
        ->name('activation.token.form');

    Route::post('/activation-token', [AuthController::class, 'createActivationToken'])
        ->name('activation.token.create');
});




 

Route::get('/activate/{user}', [UserController::class, 'activate'])
    ->name('users.activate')
    ->middleware('signed'); // exige firma válida

// Ruta pública para usar el hash
Route::get('/register/student/{hash}', [PublicRegistrationController::class, 'show'])
    ->name('public.register.student');

Route::post('/register/student/{hash}', [PublicRegistrationController::class, 'store'])
    ->name('public.register.student.store');

Route::get('/register/welcome', [PublicRegistrationController::class, 'welcome'])
    ->name('public.register.welcome');

// ----------------------------------
// Panel de administración (solo admin)
// ----------------------------------
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/', [AdministratorController::class, 'index'])->name('dashboard');

    // =====================================================================
    // Cursos
    // =====================================================================
    Route::resource('courses', CourseController::class);
    Route::get('courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
    Route::post('courses/{course}/assign-students', [CourseController::class, 'assignStudents'])->name('courses.assign-students');

    // Cursos → Videos por curso
    Route::get('courses/{course}/videos-panel', [VideoController::class,  'videosPanel'])->name('courses.videos.panel');
    Route::get('courses/{course}/videos/videos-list', [VideoController::class, 'list'])->name('courses.videos.list');
    Route::post('courses/{course}/videos/reorder', [VideoController::class, 'reorderVideos'])->name('courses.videos.reorder');
    Route::delete('courses/{course}/videos/{video}', [VideoController::class, 'deleteVideo'])->name('courses.videos.delete');

    // Cursos → Lecciones (panel y operaciones por curso)
    Route::get('courses/{course}/lessons-panel', [LessonController::class, 'lessonsPanel'])->name('courses.lessons.panel');
    Route::get('courses/{course}/lessons/list', [LessonController::class, 'list'])->name('courses.lessons.list');
    Route::post('courses/{course}/lessons/reorder', [LessonController::class, 'reorderLessons'])->name('courses.lessons.reorder');
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'deleteLesson'])->name('courses.lessons.delete');

    // =====================================================================
    // Lecciones
    // =====================================================================
    Route::resource('lessons', LessonController::class);

    // Lecciones → Videos asignados
    Route::post('/lessons/{lesson}/videos', [LessonVideosController::class, 'attach'])->name('lessons.videos.attach');
    Route::delete('lessons/{lesson}/videos/{video}', [LessonVideosController::class, 'detach'])->name('lessons.videos.detach');
    Route::post('lessons/{lesson}/videos/reorder', [LessonVideosController::class, 'reorder'])->name('lessons.videos.reorder');

    /*Evaluations*/
    // Lecciones → Evaluaciones asignadas
    Route::post('/lessons/{lesson}/evaluations', [LessonEvaluationsController::class, 'attach'])->name('lessons.evaluations.attach');
    Route::delete('lessons/{lesson}/evaluations/{evaluation}', [LessonEvaluationsController::class, 'detach'])->name('lessons.evaluations.detach');
    Route::post('lessons/{lesson}/evaluations/reorder', [LessonEvaluationsController::class, 'reorder'])->name('lessons.evaluations.reorder');

    // =====================================================================
    // Videos
    // =====================================================================
    //Route::resource('courses.videos', VideoController::class);
    Route::get('videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');
    
    Route::resource('videos', VideoController::class);
    
    Route::get('/videos/by-course/{course}', [VideoController::class, 'getByCourse'])->name('videos.by-course');

    // Videos → Materiales
    // Listar materiales de un video (usada por fetchMaterials)
    Route::get('/videos/{video}/materials', [VideoMaterialController::class, 'index'])->name('videos.materials.index');
    // Guardar uno o varios materiales (usada por addMaterial)
    Route::post('/videos/{video}/materials', [VideoMaterialController::class, 'store'])->name('videos.materials.store');
    // Eliminar material individual (usada por removeMaterial)
    Route::delete('/videos/{video}/materials/{material}', [VideoMaterialController::class, 'destroy'])->name('videos.materials.destroy');
    Route::put('/videos/{video}/materials/{material}', [VideoMaterialController::class, 'update'])->name('videos.materials.update');

    // Videos → Recursos (resource anidado)
    Route::resource('videos.resources', VideoResourcesController::class)
        ->parameters(['videos' => 'video']); // /admin/videos/{video}/resources/{resource}

    // Videos → Subtítulos (captions)
    Route::prefix('videos/{video}')
        ->name('videos.')
        ->scopeBindings()
        ->group(function () {
            Route::get('captions',              [VideoCaptionsController::class, 'index'])->name('captions.index');
            Route::post('captions',             [VideoCaptionsController::class, 'store'])->name('captions.store');
            Route::get('captions/{caption}',    [VideoCaptionsController::class, 'show'])->name('captions.show');
            Route::put('captions/{caption}',    [VideoCaptionsController::class, 'update'])->name('captions.update');
            Route::delete('captions/{caption}', [VideoCaptionsController::class, 'destroy'])->name('captions.destroy');
        });

    // =====================================================================
    // Evaluaciones
    // =====================================================================
    Route::get('/evaluations/videos/combo/{course}', [EvaluationController::class, 'videosByCourse'])->name('evaluations.videos.combo');
    Route::get('/evaluations/lessons/combo/{course}', [EvaluationController::class, 'lessonsByCourse'])->name('evaluations.lessons.combo');
    Route::get('/evaluations/by-course/{course}', [EvaluationController::class, 'getByCourse'])->name('evaluations.by-course');
    Route::resource('evaluations', EvaluationController::class);

    // Evaluaciones → Preguntas
    Route::prefix('evaluations/{evaluation}/questions')->name('evaluation-questions.')->group(function () {
        Route::get('/',                 [EvaluationQuestionController::class, 'index'])->name('index');
        Route::get('create',            [EvaluationQuestionController::class, 'create'])->name('create');
        Route::post('/',                [EvaluationQuestionController::class, 'store'])->name('store');
        Route::get('{question}/edit',   [EvaluationQuestionController::class, 'edit'])->name('edit');
        Route::put('{question}',        [EvaluationQuestionController::class, 'update'])->name('update');
        Route::delete('{question}',     [EvaluationQuestionController::class, 'destroy'])->name('destroy');
        Route::post('reorder',          [EvaluationQuestionController::class, 'reorder'])->name('reorder');
        Route::get('preview',           [EvaluationQuestionController::class, 'preview'])->name('preview');
    });

    //Archivos

      // Archivos por evaluación
        Route::get('evaluations/{evaluation}/files', [EvaluationFileController::class, 'index'])
            ->name('evaluations.files.index');

        Route::post('evaluations/{evaluation}/files', [EvaluationFileController::class, 'store'])
            ->name('evaluations.files.store');

        Route::get('evaluations/{evaluation}/files/{file}', [EvaluationFileController::class, 'show'])
            ->name('evaluations.files.show');

        Route::put('evaluations/{evaluation}/files/{file}', [EvaluationFileController::class, 'update'])
            ->name('evaluations.files.update');

        Route::delete('evaluations/{evaluation}/files/{file}', [EvaluationFileController::class, 'destroy'])
            ->name('evaluations.files.destroy');

        Route::post('evaluations/{evaluation}/files/reorder', [EvaluationFileController::class, 'reorder'])
            ->name('evaluations.files.reorder');

        Route::get('evaluations/{evaluation}/files/{file}/download', [EvaluationFileController::class, 'download'])
            ->name('evaluations.files.download');

    // Evaluaciones → Submissions de usuarios
    Route::get('evaluation-users/course/{course}/index', [EvaluationUsersController::class, 'byCourse'])->name('evaluation-users.course.index');
    Route::resource('evaluation-users', EvaluationUsersController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::put('evaluation-users/{evaluation_user}/status', [EvaluationUsersController::class, 'updateStatus'])->name('evaluation-users.update-status');

    Route::get(
    '/evaluation-users/course/{course}/user/{user}',
    [EvaluationUsersController::class, 'byUserAndCourse']
)->name('evaluation-users.byUserAndCourse');

    Route::get(
    '/evaluation-users/user/{user}',
    [EvaluationUsersController::class, 'byUser']
)->name('evaluation-users.byUser');


    Route::get('/evaluation-users', [EvaluationUsersController::class, 'all'])
    ->name('evaluation-users.all');


    // =====================================================================
    // Usuarios y Roles
    // =====================================================================
    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');
    Route::post('/users/{user}/activation/send', [UserController::class, 'sendActivation'])->name('users.activation.send');

    // =====================================================================
    // Profesores
    // =====================================================================
    Route::get('teachers/list', [TeacherController::class, 'list'])->name('teachers.list');
    Route::resource('teachers', TeacherController::class);

  // =====================================================================
// Estudiantes
// =====================================================================
// Primero rutas específicas
Route::get('students-list',        [StudentController::class, 'list'])->name('students.list');
Route::get('students/search',      [StudentController::class, 'search'])->name('students.search');
Route::get('students-search/{id}', [StudentController::class, 'searchById'])->name('students.searchById');

// Resource sin create/edit/show
Route::resource('students', StudentController::class)
    ->except(['create','edit','show'])
    ->parameters(['students' => 'user']);

// Luego las que tienen {user}, con restricción
Route::get('students/create',           [StudentController::class, 'create'])->name('students.create');
Route::get('students/{user}',           [StudentController::class, 'show'])->name('students.show')->whereNumber('user');
Route::get('students/{user}/edit',      [StudentController::class, 'edit'])->name('students.edit')->whereNumber('user');
    // =====================================================================
    // Otros recursos (Catálogos/Operaciones)
    // =====================================================================
    Route::resource('certificates', CertificateController::class);
    Route::resource('distributors', DistributorController::class);
    Route::resource('subscriptions', SubscriptionController::class);

    // Opciones (catálogos)
    Route::get('options/currencies',      [CurrencyController::class, 'options']);
    Route::get('options/payment_type',    [PaymentTypeController::class, 'options']);
    Route::get('options/payment_status',  [PaymentStatusController::class, 'options']);

    // =====================================================================
    // Activaciones
    // =====================================================================
    Route::post('/activations', [ActivationsController::class, 'store'])->name('activations.store');

    Route::post('/activations/{activation}/resend', [ActivationsController::class, 'resend'])
    ->name('activations.resend');

 Route::patch('/activations/{activation}/toggle', [ActivationsController::class, 'toggle'])
    ->name('activations.toggle');

    Route::resource('activations', ActivationsController::class);
    //Extraclases
     Route::resource('extras', ExtraClassController::class);



// =====================================================================
// Comentarios de Video (Admin)
// =====================================================================
    Route::get('video-comments',                [VideoCommentsController::class, 'index'])->name('video-comments.index');
    Route::patch('video-comments/{comment}/toggle', [VideoCommentsController::class, 'toggle'])->name('video-comments.toggle');
    Route::delete('video-comments/{comment}',   [VideoCommentsController::class, 'destroy'])->name('video-comments.destroy');


Route::get('/activities', [AdminActivityController::class, 'index'])->name('activities.index');

    // JSON para datatable (axios)
    Route::get('/activities/list', [AdminActivityController::class, 'list'])->name('activities.list');

    // Autocomplete de usuarios (axios)
    Route::get('/activities/users-search', [AdminActivityController::class, 'usersSearch'])->name('activities.users_search');

    // Detalle (dinámica al final o con whereNumber)
    Route::get('/activities/{activity}', [AdminActivityController::class, 'show'])
        ->whereNumber('activity')
        ->name('activities.show');


});

// ----------------------------------
// Panel del estudiante (solo student)
// ----------------------------------
 Route::get('/u/{nickname?}', [FrontendProfileController::class, 'show'])
    ->name('dashboard.profile.show');

Route::middleware(['auth', 'role:student'])
    ->prefix('frontend')
    ->name('dashboard.')
    ->group(function () {
        // Dashboard
        Route::get('/', [FrontendDashboardController::class, 'index'])->name('index');

        // Perfil del estudiante
        Route::get('/profile', [FrontendProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [FrontendProfileController::class, 'update'])->name('profile.update');
       
        // Seguridad
        Route::get('/security', [FrontendSecurityController::class, 'edit'])->name('security.edit');
        Route::put('/security', [FrontendSecurityController::class, 'update'])->name('security.update');

        // Configuración de idioma
        Route::post('/user/update-locale', [UserController::class, 'updateLocale'])->name('user.update-locale');
        Route::post('/locale', function (Request $request) {
            $request->validate(['locale' => 'required|in:es,en']);
            $locale = $request->input('locale');
            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
            $request->session()->put('locale', $locale);
            return back();
        })->middleware(['auth'])->name('locale.update');

        // Distribuidores
        Route::get('/distributors', [FrontendDistributorController::class, 'index'])->name('distributors.index');

        // Cursos
        Route::get('/courses', [FrontendCoursesController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', [FrontendLessonsController::class, 'index'])
            ->whereNumber('course')
            ->name('courses.show');

        // Cursos → Lecciones
        Route::get('/courses/{course}/lessons', [FrontendLessonsController::class, 'index'])
            ->whereNumber('course')
            ->name('courses.lessons.index');

        Route::get('/courses/{course}/lessons/{lesson}', [FrontendLessonsController::class, 'show'])
            ->whereNumber('course')
            ->whereNumber('lesson')
            ->name('courses.lessons.show');

        // Cursos → Lecciones → Videos
        Route::get('/courses/{course}/lessons/{lesson}/videos/{video}',
            [\App\Http\Controllers\Frontend\LessonsController::class, 'showVideo']
        )
            ->whereNumber(['course','lesson','video'])
            ->name('courses.lessons.videos.show');

        // Cursos → Videos
        Route::get('/courses/{course}/videos/{video}', [FrontendCoursesController::class, 'videoDetail'])
            ->name('courses.videos.show');
 


           Route::get('/courses/{course}/videos', [FrontendCourseVideosController::class, 'flatIndex'])
            ->name('courses.videos.flat');

        // Cursos → Evaluaciones


        Route::get('/courses/{course}/evaluations', [FrontendEvaluationController::class, 'index'])
            ->name('courses.evaluations.index');
 
  
        Route::put('/courses/{course}/evaluations/{evaluation}', [FrontendEvaluationController::class, 'update'])->name('courses.evaluations.update');
        Route::get('courses/{course}/evaluations/{evaluation}/show', [FrontendEvaluationController::class, 'show'])->name('courses.evaluations.show');
      


        //Acciones del usuario para subir la
        Route::get('courses/{course}/evaluations/{evaluation}/preview', [FrontendEvaluationUsersController::class, 'preview'])
            ->name('courses.evaluations.evaluation.preview');

          Route::post('courses/{course}/evaluations/{evaluation}/retry', [FrontendEvaluationUsersController::class, 'retry'])->name('courses.evaluations.retry');
        
        Route::get('/courses/{course}/evaluations/{evaluation}/download', [FrontendEvaluationUsersController::class, 'download'])
            ->name('courses.evaluations.download');

         
        Route::get('/evaluations/{evaluation}/thank-you', [FrontendEvaluationUsersController::class, 'thankyou'])
            ->name('evaluations.thankyou');

        Route::delete('/evaluation-users/by-evaluation', [FrontendEvaluationUsersController::class, 'destroyByEvaluation'])->name('evaluation-users.destroy-by-evaluation');

        Route::resource('evaluation-users', FrontendEvaluationUsersController::class);



        // Videos
        Route::get('/videos/{course}/video/{video}', [FrontendVideoController::class, 'show'])->name('videos.video.show');
        Route::get('/videos/{course}/list', [FrontendVideoController::class, 'listCourseVideos'])->name('videos.list');
        Route::get('/videos/{video}/resources', [FrontendVideosResourcesController::class, 'index'])->name('videos.resources.index');
        Route::get('/videos/{video}/resources/{resource}', [FrontendVideosResourcesController::class, 'show'])->name('videos.resources.show');

        // Videos → Actividad
        Route::post('/video-activity', [VideoActivityController::class, 'store'])->name('video.activity');

        // Cursos → Actividad
        Route::post('/courses/course-activity', [ActivityController::class, 'courseEnded'])->name('courses.activity');
        Route::post('/courses/video-activity', [ActivityController::class, 'videoEnded'])->name('video.activity');

         Route::post('/courses/lesson-activity', [ActivityController::class, 'lessonEnded'])->name('lesson.activity');

        // Ruta para servir videos protegidos
        Route::get('/videos/secure/{filename}', [FrontendCoursesController::class, 'streamProtectedVideo'])
            ->name('videos.secure');


        Route::prefix('/video-comments')->group(function () {
            // Listar comentarios por video
            Route::get('/video/{videoId}', [FrontendVideoCommentsController::class, 'index'])
                ->name('video-comments.index');

            // Crear comentario (requiere login)
            Route::post('/', [FrontendVideoCommentsController::class, 'store'])
                ->name('video-comments.store');


            Route::get('{parentId}/replies', [VideoCommentsController::class, 'replies'])->name('replies');

            // Like / Dislike (requieren login)
            Route::post('/{id}/like', [FrontendVideoCommentsController::class, 'like'])
                ->name('video-comments.like');

            Route::post('/{id}/dislike', [FrontendVideoCommentsController::class, 'dislike'])
                ->name('video-comments.dislike');
        });

        Route::get('/extras', [FrontendExtraClassController::class, 'index'])->name('extras.index');
        Route::get('/extras/{extra}', [FrontendExtraClassController::class, 'show'])->name('extras.show');

        
            Route::get('/blog', [FrontendBlogController::class, 'index'])->name('blog.index');
            Route::get('/blog/{slug}', [FrontendBlogController::class, 'show'])->name('blog.show');
    });
