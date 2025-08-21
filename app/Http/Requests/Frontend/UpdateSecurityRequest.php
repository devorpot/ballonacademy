<?php 
namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecurityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ya viene con auth + role:student por middleware, pero dejamos true:
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        // Si tienes una lista de locales válidos en config/locales.php, úsala:
        $locales = array_keys(config('locales', ['es' => 'Español', 'en' => 'English']));

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'locale'   => ['required', Rule::in($locales)],
            // password opcional; si se envía, mínimo 8 y confirmación
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'locale.in' => 'El idioma seleccionado no es válido.',
        ];
    }
}
