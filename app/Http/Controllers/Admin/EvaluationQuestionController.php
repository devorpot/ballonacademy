<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EvaluationQuestionController extends Controller
{
    public function index(Evaluation $evaluation)
    {
        $questions = $evaluation->questions()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/EvaluationQuestions/Index', [
            'evaluation' => $evaluation,
            'questions' => $questions,
        ]);
    }

    public function create(Evaluation $evaluation)
    {
        return Inertia::render('Admin/EvaluationQuestions/Create', [
            'evaluation' => $evaluation
        ]);
    }

    public function store(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'type' => 'required|boolean',
            'question' => 'required|string|max:255',
            'options_json' => 'nullable|array',
            'options_json.*.label' => 'required_with:options_json|string|max:255',
            'options_json.*.value' => 'required_with:options_json|integer',
            'response_text' => 'nullable|string|max:1000',
            'response_option' => 'nullable|integer',
            'order' => 'nullable|integer|min:1',
            'status' => 'required|boolean',
            'points' => 'required|integer|min:1|max:100'
        ]);

        $validated['evaluation_id'] = $evaluation->id;
        $validated['order'] = $validated['order'] ?? ($evaluation->questions()->max('order') + 1);
        $validated['points'] = $request->input('points', 1);

        EvaluationQuestion::create($validated);

        return redirect()->route('admin.evaluation-questions.index', $evaluation->id)
            ->with('success', 'Pregunta agregada exitosamente');
    }

    public function edit(Evaluation $evaluation, EvaluationQuestion $question)
    {
        return Inertia::render('Admin/EvaluationQuestions/Edit', [
            'evaluation' => $evaluation,
            'question' => $question,
        ]);
    }

    public function update(Request $request, Evaluation $evaluation, EvaluationQuestion $question)
    {
        $validated = $request->validate([
            'type' => 'required|boolean',
            'question' => 'required|string|max:255',
            'options_json' => 'nullable|array',
            'options_json.*.label' => 'required_with:options_json|string|max:255',
            'options_json.*.value' => 'required_with:options_json|integer',
            'response_text' => 'nullable|string|max:1000',
            'response_option' => 'nullable|integer',
            'order' => 'nullable|integer|min:1',
            'status' => 'required|boolean',
              'points' => 'required|integer|min:1|max:100'
        ]);
        $question->update($validated);

        return redirect()->route('admin.evaluation-questions.index', $evaluation->id)
            ->with('success', 'Pregunta actualizada correctamente');
    }

    public function destroy(Evaluation $evaluation, EvaluationQuestion $question)
    {
        $question->delete();

        return redirect()->route('admin.evaluation-questions.index', $evaluation->id)
            ->with('success', 'Pregunta eliminada correctamente');
    }

    /**
     * Reordenar preguntas por drag & drop.
     */
    public function reorder(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:evaluation_questions,id',
            'order.*.order' => 'required|integer|min:1'
        ]);

        foreach ($data['order'] as $item) {
            EvaluationQuestion::where('id', $item['id'])
                ->where('evaluation_id', $evaluation->id)
                ->update(['order' => $item['order']]);
        }

        return redirect()
            ->route('admin.evaluation-questions.index', $evaluation->id)
            ->with('success', 'Orden actualizado correctamente');
    }

      public function preview(Evaluation $evaluation)
    {
        // Cargar preguntas activas asociadas a la evaluación y el curso
        $questions = $evaluation->questions()
            ->where('status', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/EvaluationQuestions/Preview', [
            'evaluation' => $evaluation->load(['user', 'course']),
            'questions' => $questions,
        ]);
    }
}
