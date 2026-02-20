<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateFavoriRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée par le middleware acheteur
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'oeuvre_id' => 'required|exists:oeuvres,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'oeuvre_id.required' => 'L\'identifiant de l\'œuvre est obligatoire',
            'oeuvre_id.exists' => 'L\'œuvre sélectionnée n\'existe pas',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'oeuvre_id' => 'œuvre',
        ];
    }
}
