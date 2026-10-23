<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VocabularyReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quality_rating' => ['required', 'integer', 'min:0', 'max:5'],
        ];
    }
}
