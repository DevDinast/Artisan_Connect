<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InitierPaiementRequest extends FormRequest
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
            'commande_id' => 'required|exists:commandes,id',
            'methode' => 'required|in:orange_money,mtn_money,moov_money,wave',
            'telephone' => 'required|string|regex:/^[0-9]{8,15}$/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'commande_id.required' => 'L\'identifiant de la commande est obligatoire',
            'commande_id.exists' => 'La commande sélectionnée n\'existe pas',
            
            'methode.required' => 'La méthode de paiement est obligatoire',
            'methode.in' => 'La méthode de paiement doit être l\'une des suivantes: orange_money, mtn_money, moov_money, wave',
            
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères',
            'telephone.regex' => 'Le numéro de téléphone n\'est pas valide',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'commande_id' => 'commande',
            'methode' => 'méthode de paiement',
            'telephone' => 'numéro de téléphone',
        ];
    }
}
