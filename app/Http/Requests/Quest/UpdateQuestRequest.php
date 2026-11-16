<?php

namespace App\Http\Requests\Quest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateQuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
            'boss' => ['sometimes', 'string', 'max:45'],
            'difficulty' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    ! $this->has('title')
                    && ! $this->has('description')
                    && ! $this->has('boss')
                    && ! $this->has('difficulty')
                ) {
                    $validator->errors()->add(
                        'request',
                        'At least one field must be provided.',
                    );
                }
            },
        ];
    }
}