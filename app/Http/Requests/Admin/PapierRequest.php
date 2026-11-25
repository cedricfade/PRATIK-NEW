<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PapierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,ID'],
            'lien' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'pieces' => ['nullable', 'array'],
            'pieces.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function donnees(): array
    {
        return [
            'COMMUNE_ID' => $this->input('commune_id') ?: null,
            'TITRE' => $this->input('titre'),
            'DESCRIPTION' => $this->input('description'),
            'LIEN' => $this->input('lien'),
            'ORDRE' => (int) $this->input('ordre'),
            'ACTIVE' => $this->boolean('active', true) ? 1 : 0,
        ];
    }
}
