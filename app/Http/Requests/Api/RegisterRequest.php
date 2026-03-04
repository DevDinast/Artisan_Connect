<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:utilisateurs,email',
            'mot_de_passe' => 'required|string|min:8|confirmed',
            'mot_de_passe_confirmation' => 'required|string|min:8',
            'role' => 'required|in:artisan,acheteur,administrateur',
            'telephone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'nom.string' => 'Le nom doit être une chaîne de caractères',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères',
            
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères',
            
            'email.required' => 'L\'email est obligatoire',
            'email.string' => 'L\'email doit être une chaîne de caractères',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Cet email est déjà utilisé',
            
            'mot_de_passe.required' => 'Le mot de passe est obligatoire',
            'mot_de_passe.string' => 'Le mot de passe doit être une chaîne de caractères',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas',
            
            'mot_de_passe_confirmation.required' => 'La confirmation du mot de passe est obligatoire',
            'mot_de_passe_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères',
            'mot_de_passe_confirmation.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères',
            
            'role.required' => 'Le rôle est obligatoire',
            'role.in' => 'Le rôle doit être : artisan, acheteur ou administrateur',
            
            'telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 20 caractères',
        ];
    }
}
