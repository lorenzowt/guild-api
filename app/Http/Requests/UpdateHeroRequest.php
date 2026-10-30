<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\HeroClass;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHeroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->name)) {
            $this->merge([
                'name' => preg_replace('/\s+/', ' ', trim($this->name)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:45',],
            'hero_class' => ['sometimes', Rule::enum(HeroClass::class),],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    !$this->has('name') &&
                    !$this->has('hero_class') 
                ) {
                    $validator->errors()->add(
                        'request',
                        'At least one field must be provided',
                    );
                }
            },
        ];
    }
}