<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PeriodeGardeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'debut' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:debut'],
        ];
    }

    public function attributes(): array
    {
        return ['debut' => 'date de début', 'fin' => 'date de fin'];
    }
}
