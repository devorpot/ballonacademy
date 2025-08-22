<?php 

// app/Http/Requests/StoreLessonRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Aplica tu política o verificación de rol si es necesario
        return true;
    }

    public function rules(): array
    {
        return [
            'order'              => ['nullable','integer','min:0'],
            'active'             => ['boolean'],
            'title'              => ['required','string','max:255'],
            'instructions'       => ['nullable','string'],
            'description_short'  => ['nullable','string','max:255'],
            'publish_on'         => ['required','date'],
            'course_id'          => ['required','exists:courses,id'],
            'teacher_id'         => ['required','exists:users,id'],

            'add_video'          => ['boolean'],
            'add_evaluation'     => ['boolean'],
            'add_materials'      => ['boolean'],

            // Condicionales: solo validar arrays si la bandera es true
            'videos'             => ['nullable','array'],
            'videos.*'           => ['integer','exists:videos,id'],

            'evaluations'        => ['nullable','array'],
            'evaluations.*'      => ['integer','exists:evaluations,id'],

            'materials'          => ['nullable','array'],
            'materials.*'        => ['integer','exists:video_materials,id'], // cambia si tu tabla es distinta
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'add_video'      => filter_var($this->add_video, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'add_evaluation' => filter_var($this->add_evaluation, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'add_materials'  => filter_var($this->add_materials, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'active'         => filter_var($this->active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->sometimes('videos', 'required', function () {
            return $this->boolean('add_video') === true;
        });

        $validator->sometimes('evaluations', 'required', function () {
            return $this->boolean('add_evaluation') === true;
        });

        $validator->sometimes('materials', 'required', function () {
            return $this->boolean('add_materials') === true;
        });
    }
}
