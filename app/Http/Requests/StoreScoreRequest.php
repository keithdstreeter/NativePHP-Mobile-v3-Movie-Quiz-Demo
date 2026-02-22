<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:20'],
            'movie_slug' => ['required', 'string', 'max:255'],
            'score' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1'],
            'played_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'A device identifier is required.',
            'username.required' => 'A username is required.',
            'movie_slug.required' => 'A movie slug is required.',
            'score.required' => 'The score is required.',
            'total.required' => 'The total questions count is required.',
            'played_at.required' => 'The played at timestamp is required.',
        ];
    }
}
