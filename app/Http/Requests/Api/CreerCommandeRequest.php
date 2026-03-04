<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreerCommandeRequest extends FormRequest
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
            'adresse_livraison' => 'required|string|max:500',
            'telephone_livraison' => 'required|string|max:20',
            'instructions_livraison' => 'sometimes|nullable|string|max:1000',
            'methode_paiement' => 'required|in:mobile_money,carte_bancaire,virement,espece',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'adresse_livraison.required' => 'L\'adresse de livraison est obligatoire',
            'adresse_livraison.string' => 'L\'adresse de livraison doit être une chaîne de caractères',
            'adresse_livraison.max' => 'L\'adresse de livraison ne doit pas dépasser 500 caractères',
            
            'telephone_livraison.required' => 'Le téléphone de livraison est obligatoire',
            'telephone_livraison.string' => 'Le téléphone de livraison doit être une chaîne de caractères',
            'telephone_livraison.max' => 'Le téléphone de livraison ne doit pas dépasser 20 caractères',
            
            'instructions_livraison.string' => 'Les instructions de livraison doivent être une chaîne de caractères',
            'instructions_livraison.max' => 'Les instructions de livraison ne doivent pas dépasser 1000 caractères',
            
            'methode_paiement.required' => 'La méthode de paiement est obligatoire',
            'methode_paiement.in' => 'La méthode de paiement doit être l\'une des suivantes: mobile_money, carte_bancaire, virement, espece',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'adresse_livraison' => 'adresse de livraison',
            'telephone_livraison' => 'téléphone de livraison',
            'instructions_livraison' => 'instructions de livraison',
            'methode_paiement' => 'méthode de paiement',
        ];
    }
}
