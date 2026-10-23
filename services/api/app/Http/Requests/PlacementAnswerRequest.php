<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlacementAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'exists:placement_questions,id'],
            'user_answer' => ['required', 'string'],
            'time_spent_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
        ];
    }
}
