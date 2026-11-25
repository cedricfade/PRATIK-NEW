<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GardeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:periode_garde,ID'],
            'entreprises' => ['required', 'array', 'min:1'],
            'entreprises.*' => ['integer', 'exists:entreprises,ID'],
        ];
    }

    public function messages(): array
    {
        return ['entreprises.required' => 'Cochez au moins une pharmacie.'];
    }
}
