<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePanierRequest extends FormRequest
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
            'quantite' => 'required|integer|min:1|max:99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quantite.required' => 'La quantité est obligatoire',
            'quantite.integer' => 'La quantité doit être un entier',
            'quantite.min' => 'La quantité doit être supérieure à 0',
            'quantite.max' => 'La quantité ne doit pas dépasser 99',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'quantite' => 'quantité',
        ];
    }
}
