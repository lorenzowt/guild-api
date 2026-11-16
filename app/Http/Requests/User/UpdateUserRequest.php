<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => ['sometimes', 'string', 'max:100', 'confirmed'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    ! $this->has('name')
                    && ! $this->has('email')
                    && ! $this->has('password')
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