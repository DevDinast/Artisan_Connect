<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ValiderOeuvreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée par le middleware admin
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'notes_validation' => 'sometimes|nullable|string|max:1000',
            'priorite' => 'sometimes|in:normale,haute',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'notes_validation.string' => 'Les notes de validation doivent être une chaîne de caractères',
            'notes_validation.max' => 'Les notes de validation ne doivent pas dépasser 1000 caractères',
            'priorite.in' => 'La priorité doit être soit "normale" soit "haute"',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'notes_validation' => 'notes de validation',
            'priorite' => 'priorité',
        ];
    }
}
