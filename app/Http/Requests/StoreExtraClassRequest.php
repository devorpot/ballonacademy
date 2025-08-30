<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtraClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta a tu política de acceso
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'extract'     => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],

            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'is_youtube'  => ['nullable', 'in:1,2'], // 1 sí, 2 no
            'youtube_url' => ['nullable', 'url', 'required_if:is_youtube,1', 'max:255'],

            // 200 MB aprox. en KB. Ajusta a tu límite real
            'video'       => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:204800'],
            'subt_str'    => ['nullable', 'file', 'mimes:vtt,text/vtt', 'max:10240'],

            'category'    => ['nullable', 'string', 'max:100'],
            'tags'        => ['nullable', 'string', 'max:255'],

            'active'      => ['nullable', 'in:1,2'],
            'order'       => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_youtube' => (int) ($this->input('is_youtube', 2)),
            'active'     => (int) ($this->input('active', 1)),
            'order'      => (int) ($this->input('order', 0)),
        ]);
    }
}
