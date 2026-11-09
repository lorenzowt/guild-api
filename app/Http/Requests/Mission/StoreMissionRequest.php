<?php

namespace App\Http\Requests\Mission;

use App\Enums\HeroStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quest_id' => [
                'required',
                'integer',
                Rule::exists('quests', 'id')
                    ->where('is_active', true),
            ],

            'hero_ids' => [
                'required',
                'array',
                'min:1',
                'max:4',
            ],

            'hero_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('heroes', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('status', HeroStatus::Available->value),
            ],
        ];
    }
}

