<?php

namespace App\Http\Requests;

use App\Rules\AllowedFeedUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => [
                'required',
                'string',
                'max:2048',
                new AllowedFeedUrl,
                Rule::unique('feeds', 'url')
                    ->where('user_id', $this->user()?->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.unique' => 'You have already added this feed.',
        ];
    }
}
