<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHabitLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'target_days' => 'required|integer|min:1',
            'color' => ['nullable','regex:/!:^#([A-Fa-f0-9]{6}$/)'],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
