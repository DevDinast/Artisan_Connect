<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvisRequest extends FormRequest
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
            'note' => 'sometimes|required|integer|min:1|max:5',
            'titre_avis' => 'sometimes|nullable|string|max:200',
            'commentaire' => 'sometimes|nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'note.required' => 'La note est obligatoire',
            'note.integer' => 'La note doit être un entier',
            'note.min' => 'La note doit être supérieure à 0',
            'note.max' => 'La note ne doit pas dépasser 5',
            
            'titre_avis.string' => 'Le titre de l\'avis doit être une chaîne de caractères',
            'titre_avis.max' => 'Le titre de l\'avis ne doit pas dépasser 200 caractères',
            
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 2000 caractères',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'note' => 'note',
            'titre_avis' => 'titre de l\'avis',
            'commentaire' => 'commentaire',
        ];
    }
}
