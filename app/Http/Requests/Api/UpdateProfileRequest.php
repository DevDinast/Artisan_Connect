<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|nullable|string|max:20',
            'avatar' => 'sometimes|nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.string' => 'Le nom doit être une chaîne de caractères',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères',
            
            'prenom.string' => 'Le prénom doit être une chaîne de caractères',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères',
            
            'telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 20 caractères',
            
            'avatar.string' => 'L\'avatar doit être une chaîne de caractères',
            'avatar.max' => 'L\'avatar ne doit pas dépasser 500 caractères',
        ];
    }
}
