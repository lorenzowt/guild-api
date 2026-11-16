<?php

namespace App\Http\Requests\Quest;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'boss' => ['required', 'string', 'max:45'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
