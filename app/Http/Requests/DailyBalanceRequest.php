<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class DailyBalanceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    protected function prepareForValidation()
    {
        // Set default dates if not provided
        $this->merge([
            'start_date' => $this->start_date ?? now()->subMonth()->format('Y-m-d'),
            'end_date' => $this->end_date ?? now()->format('Y-m-d'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        if ($key) {
            return parent::validated()[$key] ?? $default;
        }
        return parent::validated();
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'start_date.date_format' => 'The start date must be in the format YYYY-MM-DD.',
            
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_date.date_format' => 'The end date must be in the format YYYY-MM-DD.',
        ];
    }
}

