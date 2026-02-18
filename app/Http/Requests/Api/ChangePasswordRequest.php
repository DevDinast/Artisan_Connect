<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'mot_de_passe_actuel' => 'required|string',
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'mot_de_passe_confirmation' => 'required|string|min:8',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'mot_de_passe_actuel.required' => 'Le mot de passe actuel est obligatoire',
            'mot_de_passe_actuel.string' => 'Le mot de passe actuel doit être une chaîne de caractères',
            
            'mot_de_passe.required' => 'Le nouveau mot de passe est obligatoire',
            'mot_de_passe.string' => 'Le nouveau mot de passe doit être une chaîne de caractères',
            'mot_de_passe.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères',
            'mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas',
            
            'mot_de_passe_confirmation.required' => 'La confirmation du mot de passe est obligatoire',
            'mot_de_passe_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères',
            'mot_de_passe_confirmation.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères',
        ];
    }
}
