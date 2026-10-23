<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoryProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_segment_index' => ['required', 'integer', 'min:0'],
        ];
    }
}
