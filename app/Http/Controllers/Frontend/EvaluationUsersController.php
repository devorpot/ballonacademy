<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\EvaluationUser;
use App\Models\Activity;
use App\Enums\ActivityType;
use App\Enums\EvaluationUserStatus;
use App\Models\CourseActivity; 
use Illuminate\Support\Facades\DB;
 
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
 
 

class EvaluationUsersController extends Controller
{
    // Lista de evaluaciones del usuario autenticado
    public function index() 
    {
        $evaluations = EvaluationUser::where('user_id', auth()->id())->get();
        // El status_label y otros accessors están disponibles en el modelo
        return response()->json($evaluations);
    }
    
    // Guarda una nueva evaluación del usuario
    public function store(Request $request)
{
    $data = $request->validate([
        'course_id' => 'required|exists:courses,id',
        'comments'  => 'nullable|string',
        'data'      => 'required|string',
        'files'     => 'nullable|file|max:51200',
    ]);

    $data['user_id'] = auth()->id();

    return DB::transaction(function () use ($request, $data) {
        // Subir archivo (si aplica)
        $filePath = null;
        if ($request->hasFile('files')) {
            $filePath = $request->file('files')->store('evaluations/files', 'public');
        }

        // Decodificar payload de la evaluación
        $evalData = json_decode($data['data'], true);

        // Crear el envío de evaluación del usuario
        $evaluationUser = EvaluationUser::create([
            'user_id'       => $data['user_id'],
            'course_id'     => $data['course_id'],
            'comments'      => $data['comments'] ?? null,
            'files'         => $filePath,
            'data'          => $evalData,
            'evaluation_id' => $evalData['evaluation_id'] ?? null,
            'status'        => EvaluationUserStatus::SEND,
        ]);

        // Registrar actividad
        Activity::create([
            'user_id'       => $data['user_id'],
            'course_id'     => $data['course_id'],
            'video_id'      => null, // si aplica, pon el ID del video
            'evaluation_id' => $evalData['evaluation_id'] ?? null,
            // IMPORTANTE: ajusta el case del enum a tu definición real
            // Ejemplos posibles: ActivityType::EVALUATION_CREATED, ::EVALUATION_SUBMITTED, ::EVALUATION_USER_CREATED
            'type'          => ActivityType::EVALUATION_SEND,
            'description'   => sprintf(
                'El usuario %s envió una nueva evaluación (EvaluationUser #%d) para el curso #%d.',
                $request->user()->name ?? ('user:'.$data['user_id']),
                $evaluationUser->id,
                (int) $data['course_id']
            ),
        ]);

        // Redirección a la pantalla de agradecimiento
        return redirect()->route('dashboard.evaluations.thankyou', [
            'evaluation' => $evalData['evaluation_id'] ?? null,
        ]);
    });
}

    // Muestra una evaluación en específico
    public function show($id)
    {
        $evaluation = EvaluationUser::findOrFail($id);
        return response()->json($evaluation);
    }

    // Actualiza una evaluación (opcional)
    public function update(Request $request, $id)
    {
        $evaluation = EvaluationUser::findOrFail($id);

        $data = $request->validate([
            'comments' => 'nullable|string',
            'data' => 'nullable|array',
            'status' => 'nullable|integer|in:111,222,333,999',
            'files' => 'nullable|string',
            'approved_user_id' => 'nullable|exists:users,id',
        ]);

        // Si recibes un status, castealo al Enum
        if (isset($data['status'])) {
            $data['status'] = EvaluationUserStatus::from($data['status']);
        }

        $evaluation->update($data);

        return response()->json($evaluation);
    }

    // Elimina una evaluación (opcional)
public function destroy($id)
{
    $evaluation = EvaluationUser::findOrFail($id);

    // Guardar info antes de eliminar
    $userId       = $evaluation->user_id;
    $courseId     = $evaluation->course_id;
    $evaluationId = $evaluation->evaluation_id;

    $evaluation->delete();

    // Registrar la actividad
    Activity::create([
        'user_id'       => $userId,
        'course_id'     => $courseId,
        'evaluation_id' => $evaluationId,
        'type'          => ActivityType::EVALUATION_DELETE,
        'description'   => sprintf(
            'El usuario %s eliminó su evaluación #%d del curso #%d.',
            auth()->user()->name ?? ('user:'.$userId),
            $id,
            (int) $courseId
        ),
    ]);

    return response()->json(['message' => 'Evaluación eliminada']);
}

    // Pantalla de agradecimiento tras enviar evaluación
    public function thankyou($course = null, $evaluation = null)
    {



        return Inertia::render('Frontend/Evaluations/Thankyou', [
            'evaluation' => $evaluation,
            'course' => $course
        ]);
    }

    // Vista previa de la evaluación antes de enviar
    public function preview(Course $course, Evaluation $evaluation)
    {
  
        $this->authorizeEvaluation($evaluation, $course);

        $questions = $evaluation->questions()
            ->where('status', true)
            ->orderBy('order')
            ->get();

        // Checar si el usuario ya envió esta evaluación
        $userHasSubmitted = EvaluationUser::where('user_id', auth()->id())
            ->where('evaluation_id', $evaluation->id)
            ->exists();

        // Obtener la última evaluación enviada por el usuario para esta evaluación
        $lastEvaluationUser = EvaluationUser::where('user_id', auth()->id())
            ->where('evaluation_id', $evaluation->id)
            ->latest('created_at')
            ->first();


        $evaluation->load(['course:id,title', 'lesson:id,course_id,teacher_id,order,active,title,instructions,description_short,publish_on,image,image_cover']);


        return Inertia::render('Frontend/Evaluations/Preview', [
            'evaluation' => $evaluation->load(['course']),
            'course'     => $course, // <- Agrega esto
            'questions'  => $questions,
            'lesson'             => $evaluation->lesson,  
            'userHasSubmitted' => $userHasSubmitted,
            'lastEvaluationUser' => $lastEvaluationUser,
        ]);
    } 


    // Eliminar evaluación por ID de evaluación y curso (usado para "Rehacer test")
    public function destroyByEvaluation(Request $request)
{
    $userId      = auth()->id();
    $evaluationId = $request->input('evaluation_id');
    $courseId     = $request->input('course_id'); // Recibe el course_id desde el frontend

    $evaluationUser = EvaluationUser::where('user_id', $userId)
        ->where('evaluation_id', $evaluationId)
        ->first();

    if ($evaluationUser) {
        if ($evaluationUser->files && Storage::disk('public')->exists($evaluationUser->files)) {
            Storage::disk('public')->delete($evaluationUser->files);
        }

        $evaluationUser->delete();

        // Registrar la actividad
        Activity::create([
            'user_id'       => $userId,
            'course_id'     => $courseId,
            'evaluation_id' => $evaluationId,
            'type'          => ActivityType::EVALUATION_DELETE,
            'description'   => sprintf(
                'El usuario %s eliminó su evaluación #%d del curso #%d.',
                auth()->user()->name ?? ('user:'.$userId),
                $evaluationId,
                (int) $courseId
            ),
        ]);

        // Redirecciona al preview de nuevo
        return redirect()->route('dashboard.courses.evaluations.evaluation.preview', [
            'course'     => $courseId,
            'evaluation' => $evaluationId,
        ]);
    }

    // Opcional: redirige aunque no haya registro, para evitar errores en frontend
    return redirect()->route('dashboard.courses.evaluations.evaluation.preview', [
        'course'     => $courseId,
        'evaluation' => $evaluationId,
    ]);
}


    // Proteger acceso a evaluación solo si pertenece al curso
    protected function authorizeEvaluation(Evaluation $evaluation, Course $course)
    {
        if ($evaluation->course_id !== $course->id) {
            abort(403);
        }
    }

    public function retry(Course $course, Evaluation $evaluation): RedirectResponse
    {
        $userId = auth()->id();

        // Validar que la evaluación pertenece al curso
        if ((int) $evaluation->course_id !== (int) $course->id) {
            abort(403, 'Evaluación no válida para este curso.');
        }

        DB::transaction(function () use ($userId, $course, $evaluation) {
            // Traemos los envíos previos para borrar archivos y contar
            $prevSubmissions = EvaluationUser::where('user_id', $userId)
                ->where('evaluation_id', $evaluation->id)
                ->get(['id', 'files']);

            // Borrar archivos si existen
            foreach ($prevSubmissions as $submission) {
                if ($submission->files && Storage::disk('public')->exists($submission->files)) {
                    Storage::disk('public')->delete($submission->files);
                }
            }

            // Borrar envíos
            $deleted = EvaluationUser::where('user_id', $userId)
                ->where('evaluation_id', $evaluation->id)
                ->delete();

            // Registrar actividad (una sola) indicando cuántos se eliminaron
            Activity::create([
                'user_id'       => $userId,
                'course_id'     => $course->id,
                'evaluation_id' => $evaluation->id,
                'type'          => ActivityType::EVALUATION_DELETE,
                'description'   => sprintf(
                    'El usuario %s reinició la evaluación #%d del curso #%d. Envíos eliminados: %d.',
                    auth()->user()->name ?? ('user:'.$userId),
                    $evaluation->id,
                    $course->id,
                    $deleted
                ),
            ]);
        });

        return redirect()->route(
            'dashboard.courses.evaluations.evaluation.preview',
            ['course' => $course->id, 'evaluation' => $evaluation->id]
        )->with('success', 'Puedes volver a realizar la evaluación.');
    }
}
