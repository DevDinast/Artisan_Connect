<?php

namespace App\Services;

use App\Models\Oeuvre;
use App\Models\Image;
use App\Models\Categorie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as ImageProcessor;

class OeuvreService
{
    /**
     * Règles de gestion (RG06, RG07, RG09)
     */
    private const REGLES = [
        'RG06' => 'Une œuvre doit avoir au moins une image principale',
        'RG07' => 'Le prix doit être supérieur à 1000 FCFA',
        'RG09' => 'Les dimensions doivent être complètes pour la catégorie sélectionnée',
    ];

    /**
     * Créer une nouvelle œuvre pour un artisan
     */
    public function creerOeuvre(array $data, $artisanId)
    {
        try {
            DB::beginTransaction();

            // Validation des règles métier
            $this->validerReglesGestion($data);

            // Création de l'œuvre
            $oeuvre = Oeuvre::create([
                'artisan_id' => $artisanId,
                'categorie_id' => $data['categorie_id'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'prix' => $this->formatPrix($data['prix']),
                'quantite_disponible' => $data['quantite_disponible'] ?? 1,
                'dimensions' => $this->formatDimensions($data['dimensions'] ?? []),
                'materiaux' => $this->formatMateriaux($data['materiaux'] ?? []),
                'statut' => 'brouillon',
            ]);

            // Traitement des images si fournies
            if (isset($data['images']) && !empty($data['images'])) {
                $this->traiterImages($oeuvre->id, $data['images']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Œuvre créée avec succès',
                'data' => $oeuvre->load(['images', 'categorie'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une œuvre existante
     */
    public function mettreAJourOeuvre(array $data, $oeuvreId, $artisanId)
    {
        try {
            $oeuvre = Oeuvre::where('id', $oeuvreId)
                ->where('artisan_id', $artisanId)
                ->firstOrFail();

            // Vérifier que l'œuvre peut être modifiée
            if (!$this->peutModifierOeuvre($oeuvre)) {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre ne peut plus être modifiée',
                    'raison' => $oeuvre->statut
                ];
            }

            DB::beginTransaction();

            // Validation des règles métier
            $this->validerReglesGestion($data);

            // Mise à jour des champs
            $updateData = [
                'categorie_id' => $data['categorie_id'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'prix' => $this->formatPrix($data['prix']),
                'quantite_disponible' => $data['quantite_disponible'],
                'dimensions' => $this->formatDimensions($data['dimensions'] ?? []),
                'materiaux' => $this->formatMateriaux($data['materiaux'] ?? []),
            ];

            // Si l'œuvre est déjà validée, elle revient en brouillon
            if ($oeuvre->statut === 'validee') {
                $updateData['statut'] = 'brouillon';
                $updateData['motif_refus'] = null;
                $updateData['date_validation'] = null;
                $updateData['validateur_id'] = null;
            }

            $oeuvre->update($updateData);

            // Traitement des images si fournies
            if (isset($data['images']) && !empty($data['images'])) {
                $this->traiterImages($oeuvre->id, $data['images'], true);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Œuvre mise à jour avec succès',
                'data' => $oeuvre->fresh()->load(['images', 'categorie'])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer (soft delete) une œuvre
     */
    public function supprimerOeuvre($oeuvreId, $artisanId)
    {
        try {
            $oeuvre = Oeuvre::where('id', $oeuvreId)
                ->where('artisan_id', $artisanId)
                ->firstOrFail();

            // Vérifier que l'œuvre peut être supprimée
            if (!$this->peutSupprimerOeuvre($oeuvre)) {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre ne peut être supprimée',
                    'raison' => $oeuvre->statut
                ];
            }

            // Soft delete
            $oeuvre->delete();

            return [
                'success' => true,
                'message' => 'Œuvre supprimée avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Soumettre une œuvre pour validation
     */
    public function soumettreOeuvre($oeuvreId, $artisanId)
    {
        try {
            $oeuvre = Oeuvre::where('id', $oeuvreId)
                ->where('artisan_id', $artisanId)
                ->firstOrFail();

            // Vérifier que l'œuvre peut être soumise
            if (!$this->peutSoumettreOeuvre($oeuvre)) {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre ne peut être soumise',
                    'raison' => $oeuvre->statut
                ];
            }

            $oeuvre->update([
                'statut' => 'en_attente',
                'motif_refus' => null,
                'date_validation' => null,
                'validateur_id' => null,
            ]);

            return [
                'success' => true,
                'message' => 'Œuvre soumise pour validation',
                'data' => $oeuvre->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la soumission de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Traiter les images d'une œuvre
     */
    private function traiterImages($oeuvreId, array $images, bool $isUpdate = false)
    {
        try {
            // Supprimer les anciennes images si c'est une mise à jour
            if ($isUpdate) {
                Image::where('oeuvre_id', $oeuvreId)->delete();
            }

            foreach ($images as $index => $imageFile) {
                if ($imageFile instanceof UploadedFile) {
                    $this->traiterImageFile($oeuvreId, $imageFile, $index);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors du traitement des images: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un fichier image
     */
    private function traiterImageFile($oeuvreId, UploadedFile $imageFile, int $index)
    {
        try {
            // Validation du fichier
            $this->validerImageFile($imageFile);

            // Traitement de l'image
            $processedImage = $this->traiterImageProcessor($imageFile);

            // Stockage
            $chemin = $this->stockerImage($processedImage, $imageFile);

            // Enregistrement en base de données
            Image::create([
                'oeuvre_id' => $oeuvreId,
                'chemin' => $chemin,
                'type' => $index === 0 ? 'principale' : 'secondaire',
                'ordre' => $index,
            ]);

        } catch (\Exception $e) {
            throw new \Exception('Erreur lors du traitement de l\'image: ' . $e->getMessage());
        }
    }

    /**
     * Traiter l'image avec ImageProcessor
     */
    private function traiterImageProcessor(UploadedFile $imageFile)
    {
        $image = ImageProcessor::make($imageFile);

        // Redimensionnement
        $image->resize(1200, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Compression et qualité
        $image->encode('jpg', 85);

        // Watermark (optionnel)
        if (config('artisan.watermark.enabled', false)) {
            $this->ajouterWatermark($image);
        }

        return $image;
    }

    /**
     * Ajouter un watermark
     */
    private function ajouterWatermark($image)
    {
        $watermarkText = config('artisan.watermark.text', 'ArtisanConnect');
        $watermarkSize = config('artisan.watermark.size', 20);

        $image->text($watermarkText, function ($font) {
            $font->file(public_path('fonts/arial.ttf'));
            $font->size($watermarkSize);
            $font->color('#ffffff');
            $font->align('center', 'bottom');
        })->opacity(50);
    }

    /**
     * Stocker l'image
     */
    private function stockerImage($processedImage, UploadedFile $originalFile)
    {
        $fileName = time() . '_' . uniqid() . '.jpg';
        $directory = 'oeuvres/' . date('Y/m');
        
        $path = $processedImage->store($directory, 'public', [
            'disk' => 'public',
            'filename' => $fileName
        ]);

        return 'storage/' . $path;
    }

    /**
     * Valider les règles de gestion
     */
    private function validerReglesGestion(array $data)
    {
        // RG06: Au moins une image principale
        if (!isset($data['images']) || empty($data['images'])) {
            throw new \Exception(self::REGLES['RG06']);
        }

        // RG07: Prix minimum
        if (isset($data['prix']) && (float) $data['prix'] < 1000) {
            throw new \Exception(self::REGLES['RG07']);
        }

        // RG09: Dimensions complètes selon catégorie
        if (isset($data['categorie_id'])) {
            $categorie = Categorie::find($data['categorie_id']);
            if ($categorie && $this->categorieRequiertDimensions($categorie->nom)) {
                if (!isset($data['dimensions']) || 
                    empty($data['dimensions']['longueur']) || 
                    empty($data['dimensions']['largeur']) || 
                    empty($data['dimensions']['hauteur'])) {
                    throw new \Exception(self::REGLES['RG09']);
                }
            }
        }
    }

    /**
     * Vérifier si une catégorie requiert des dimensions
     */
    private function categorieRequiertDimensions($categorieNom)
    {
        $categoriesRequissantDimensions = [
            'Sculpture', 'Meubles', 'Miroirs', 'Cadres'
        ];
        
        return in_array($categorieNom, $categoriesRequissantDimensions);
    }

    /**
     * Vérifier si une œuvre peut être modifiée
     */
    private function peutModifierOeuvre($oeuvre)
    {
        return in_array($oeuvre->statut, ['brouillon', 'refusee']);
    }

    /**
     * Vérifier si une œuvre peut être supprimée
     */
    private function peutSupprimerOeuvre($oeuvre)
    {
        return !in_array($oeuvre->statut, ['en_attente', 'epuisee']);
    }

    /**
     * Vérifier si une œuvre peut être soumise
     */
    private function peutSoumettreOeuvre($oeuvre)
    {
        if ($oeuvre->statut !== 'brouillon') {
            return false;
        }

        // Vérifier qu'il y a au moins une image
        $hasImages = Image::where('oeuvre_id', $oeuvre->id)->exists();
        if (!$hasImages) {
            throw new \Exception(self::REGLES['RG06']);
        }

        return true;
    }

    /**
     * Valider un fichier image
     */
    private function validerImageFile(UploadedFile $file)
    {
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Format d\'image non autorisé. Formats acceptés: JPG, PNG, WebP');
        }

        if ($file->getSize() > $maxSize) {
            throw new \Exception('L\'image ne doit pas dépasser 5MB');
        }
    }

    /**
     * Formater le prix
     */
    private function formatPrix($prix)
    {
        return number_format((float) $prix, 2, '.', '');
    }

    /**
     * Formater les dimensions en JSON
     */
    private function formatDimensions(array $dimensions)
    {
        if (empty($dimensions)) {
            return null;
        }

        return [
            'longueur' => $dimensions['longueur'] ?? null,
            'largeur' => $dimensions['largeur'] ?? null,
            'hauteur' => $dimensions['hauteur'] ?? null,
            'profondeur' => $dimensions['profondeur'] ?? null,
            'poids' => $dimensions['poids'] ?? null,
        ];
    }

    /**
     * Formater les matériaux en JSON
     */
    private function formatMateriaux(array $materiaux)
    {
        if (empty($materiaux)) {
            return null;
        }

        return array_values(array_filter($materiaux));
    }

    /**
     * Obtenir les statistiques pour un artisan
     */
    public function getStatistiquesArtisan($artisanId)
    {
        try {
            $stats = [
                'oeuvres_count' => Oeuvre::where('artisan_id', $artisanId)->count(),
                'oeuvres_validees' => Oeuvre::where('artisan_id', $artisanId)->where('statut', 'validee')->count(),
                'oeuvres_en_attente' => Oeuvre::where('artisan_id', $artisanId)->where('statut', 'en_attente')->count(),
                'ventes_count' => DB::table('transactions')
                    ->where('artisan_id', $artisanId)
                    ->where('statut', 'payee')
                    ->count(),
                'revenus_total' => DB::table('transactions')
                    ->where('artisan_id', $artisanId)
                    ->where('statut', 'payee')
                    ->sum('montant_artisan'),
                'moyenne_prix' => Oeuvre::where('artisan_id', $artisanId)->avg('prix'),
                'derniere_vente' => DB::table('transactions')
                    ->where('artisan_id', $artisanId)
                    ->where('statut', 'payee')
                    ->latest('created_at')
                    ->first(),
            ];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ];
        }
    }
}
