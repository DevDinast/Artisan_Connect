<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RefuserOeuvreRequest extends FormRequest
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
            'motif_refus' => 'required|string|max:1000',
            'motif_refus_code' => 'sometimes|required|in:qualite,contenu,photo,autre',
            'notes_admin' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motif_refus.required' => 'Le motif de refus est obligatoire',
            'motif_refus.string' => 'Le motif de refus doit être une chaîne de caractères',
            'motif_refus.max' => 'Le motif de refus ne doit pas dépasser 1000 caractères',
            'motif_refus_code.required' => 'Le code de motif de refus est obligatoire',
            'motif_refus_code.in' => 'Le code de motif doit être l\'un des suivants: qualite, contenu, photo, autre',
            'notes_admin.string' => 'Les notes administrateur doivent être une chaîne de caractères',
            'notes_admin.max' => 'Les notes administrateur ne doivent pas dépasser 1000 caractères',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'motif_refus' => 'motif de refus',
            'motif_refus_code' => 'code de motif',
            'notes_admin' => 'notes administrateur',
        ];
    }
}
