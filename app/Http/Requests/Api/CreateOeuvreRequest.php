<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateOeuvreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée par le middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'categorie_id' => 'required|exists:categories,id',
            'titre' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'prix' => 'required|numeric|min:1000|max:999999.99',
            'quantite_disponible' => 'required|integer|min:1|max:999',
            'dimensions' => 'sometimes|array',
            'dimensions.longueur' => 'sometimes|nullable|numeric|min:0.1|max:9999.9',
            'dimensions.largeur' => 'sometimes|nullable|numeric|min:0.1|max:9999.9',
            'dimensions.hauteur' => 'sometimes|nullable|numeric|min:0.1|max:9999.9',
            'dimensions.profondeur' => 'sometimes|nullable|numeric|min:0.1|max:9999.9',
            'dimensions.poids' => 'sometimes|nullable|numeric|min:0.1|max:9999.9',
            'materiaux' => 'sometimes|array|max:10',
            'materiaux.*' => 'string|max:50',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'categorie_id.required' => 'La catégorie est obligatoire',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas',
            
            'titre.required' => 'Le titre est obligatoire',
            'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
            
            'description.required' => 'La description est obligatoire',
            'description.max' => 'La description ne doit pas dépasser 2000 caractères',
            
            'prix.required' => 'Le prix est obligatoire',
            'prix.numeric' => 'Le prix doit être un nombre',
            'prix.min' => 'Le prix doit être supérieur à 1000 FCFA',
            'prix.max' => 'Le prix ne doit pas dépasser 999999.99 FCFA',
            
            'quantite_disponible.required' => 'La quantité disponible est obligatoire',
            'quantite_disponible.integer' => 'La quantité doit être un entier',
            'quantite_disponible.min' => 'La quantité doit être supérieure à 0',
            'quantite_disponible.max' => 'La quantité ne doit pas dépasser 999',
            
            'dimensions.array' => 'Les dimensions doivent être un tableau',
            'dimensions.longueur.numeric' => 'La longueur doit être un nombre',
            'dimensions.longueur.min' => 'La longueur doit être positive',
            'dimensions.longueur.max' => 'La longueur ne doit pas dépasser 9999.9',
            'dimensions.largeur.numeric' => 'La largeur doit être un nombre',
            'dimensions.largeur.min' => 'La largeur doit être positive',
            'dimensions.largeur.max' => 'La largeur ne doit pas dépasser 9999.9',
            'dimensions.hauteur.numeric' => 'La hauteur doit être un nombre',
            'dimensions.hauteur.min' => 'La hauteur doit être positive',
            'dimensions.hauteur.max' => 'La hauteur ne doit pas dépasser 9999.9',
            'dimensions.profondeur.numeric' => 'La profondeur doit être un nombre',
            'dimensions.profondeur.min' => 'La profondeur doit être positive',
            'dimensions.profondeur.max' => 'La profondeur ne doit pas dépasser 9999.9',
            'dimensions.poids.numeric' => 'Le poids doit être un nombre',
            'dimensions.poids.min' => 'Le poids doit être positif',
            'dimensions.poids.max' => 'Le poids ne doit pas dépasser 9999.9',
            
            'materiaux.array' => 'Les matériaux doivent être un tableau',
            'materiaux.max' => 'Le nombre de matériaux ne doit pas dépasser 10',
            'materiaux.*.string' => 'Chaque matériau doit être une chaîne de caractères',
            'materiaux.*.max' => 'Chaque matériau ne doit pas dépasser 50 caractères',
            
            'images.required' => 'Au moins une image est obligatoire (RG06)',
            'images.array' => 'Les images doivent être un tableau',
            'images.min' => 'Au moins une image est requise',
            'images.max' => 'Le nombre d\'images ne doit pas dépasser 10',
            'images.*.file' => 'Chaque image doit être un fichier',
            'images.*.image' => 'Le fichier doit être une image',
            'images.*.mimes' => 'Format d\'image non autorisé. Formats acceptés: JPG, PNG, WebP',
            'images.*.max' => 'Chaque image ne doit pas dépasser 5MB',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'categorie_id' => 'catégorie',
            'quantite_disponible' => 'quantité disponible',
            'dimensions.longueur' => 'longueur',
            'dimensions.largeur' => 'largeur',
            'dimensions.hauteur' => 'hauteur',
            'dimensions.profondeur' => 'profondeur',
            'dimensions.poids' => 'poids',
        ];
    }
}
