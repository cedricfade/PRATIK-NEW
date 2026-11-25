<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TitrologieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_parution' => ['required', 'date'],
            'unes' => ['required', 'array', 'min:1', 'max:60'],
            'unes.*.source_id' => ['required', 'integer', 'exists:source_news,ID'],
            'unes.*.type_news_id' => ['required', 'integer', 'exists:type_news,ID'],
            'unes.*.rang' => ['nullable', 'integer', 'min:0', 'max:999'],
            'unes.*.numero' => ['nullable', 'string', 'max:25'],
            'unes.*.image' => ['required', 'image', 'max:8192'],
        ];
    }

    public function attributes(): array
    {
        return ['unes.*.source_id' => 'journal', 'unes.*.image' => 'image de la une', 'unes.*.type_news_id' => 'périodicité'];
    }
}
