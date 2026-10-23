<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'learning_reason' => ['required', 'string', 'in:travel,career,education,culture,personal'],
            'daily_goal_minutes' => ['required', 'integer', 'min:5', 'max:120'],
            'experience_level' => ['required', 'string', 'in:zero,basics,intermediate'],
            'explanation_dialect' => ['required', 'string', 'in:egyptian_arabic,msa'],
            'target_variant' => ['required', 'string', 'in:spain,latin_america'],
        ];
    }
}
