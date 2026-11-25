<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AnnonceurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'categorie' => ['required', 'in:Immobilier,Restaurant'],
            'agent_tel' => ['nullable', 'string', 'max:50'],
            'agent_cel' => ['nullable', 'string', 'max:50'],
            'entreprise_id' => ['nullable', 'required_without:nouvelle_entreprise', 'integer', 'exists:entreprises,ID'],
            'nouvelle_entreprise' => ['nullable', 'required_without:entreprise_id', 'string', 'max:300'],
            'commune_id' => ['nullable', 'integer', 'exists:communes,ID'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nom', 'password' => 'mot de passe', 'entreprise_id' => 'entreprise', 'nouvelle_entreprise' => 'nom de l\'entreprise'];
    }

    public function messages(): array
    {
        return [
            'entreprise_id.required_without' => 'Choisissez une entreprise existante ou saisissez le nom d\'une nouvelle.',
            'nouvelle_entreprise.required_without' => 'Choisissez une entreprise existante ou saisissez le nom d\'une nouvelle.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
        ];
    }
}
