<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image as ImageProcessor;

class ImageService
{
    /**
     * Traiter et stocker une image
     */
    public function traiterImage(UploadedFile $file, int $oeuvreId, int $ordre = 0, string $type = 'secondaire')
    {
        try {
            // Validation du fichier
            $this->validerImageFile($file);

            // Traitement de l'image
            $processedImage = $this->traiterImageProcessor($file);

            // Stockage
            $chemin = $this->stockerImage($processedImage, $file);

            // Enregistrement en base de données
            $image = Image::create([
                'oeuvre_id' => $oeuvreId,
                'chemin' => $chemin,
                'type' => $type,
                'ordre' => $ordre,
            ]);

            return [
                'success' => true,
                'image' => $image,
                'url' => Storage::url($chemin)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement de l\'image: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter plusieurs images
     */
    public function traiterMultipleImages(array $files, int $oeuvreId)
    {
        $results = [];
        $images = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $type = $index === 0 ? 'principale' : 'secondaire';
                $result = $this->traiterImage($file, $oeuvreId, $index, $type);
                
                if ($result['success']) {
                    $images[] = $result['image'];
                }
                
                $results[] = $result;
            }
        }

        return [
            'success' => !empty($images),
            'images' => $images,
            'results' => $results
        ];
    }

    /**
     * Supprimer une image
     */
    public function supprimerImage($imageId, $artisanId)
    {
        try {
            $image = Image::with('oeuvre')->findOrFail($imageId);

            // Vérifier que l'image appartient à l'artisan
            if ($image->oeuvre->artisan_id !== $artisanId) {
                return [
                    'success' => false,
                    'message' => 'Cette image ne vous appartient pas'
                ];
            }

            // Supprimer le fichier
            if (Storage::disk('public')->exists($image->chemin)) {
                Storage::disk('public')->delete($image->chemin);
            }

            // Supprimer l'enregistrement
            $image->delete();

            return [
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Image non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'image: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Réorganiser l'ordre des images
     */
    public function reorganiserImages(array $ordreImages, int $oeuvreId, $artisanId)
    {
        try {
            foreach ($ordreImages as $position => $imageId) {
                $image = Image::with('oeuvre')->findOrFail($imageId);

                // Vérifier que l'image appartient à l'artisan
                if ($image->oeuvre->artisan_id !== $artisanId) {
                    return [
                        'success' => false,
                        'message' => 'Une des images ne vous appartient pas'
                    ];
                }

                $image->update(['ordre' => $position]);
            }

            return [
                'success' => true,
                'message' => 'Ordre des images mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la réorganisation des images: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Définir une image comme principale
     */
    public function definirImagePrincipale($imageId, $artisanId)
    {
        try {
            $image = Image::with('oeuvre')->findOrFail($imageId);

            // Vérifier que l'image appartient à l'artisan
            if ($image->oeuvre->artisan_id !== $artisanId) {
                return [
                    'success' => false,
                    'message' => 'Cette image ne vous appartient pas'
                ];
            }

            // Mettre toutes les autres images en secondaire
            Image::where('oeuvre_id', $image->oeuvre->id)
                ->where('id', '!=', $imageId)
                ->update(['type' => 'secondaire']);

            // Mettre l'image sélectionnée en principale
            $image->update(['type' => 'principale']);

            return [
                'success' => true,
                'message' => 'Image principale définie avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Image non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la définition de l\'image principale: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter l'image avec ImageProcessor
     */
    private function traiterImageProcessor(UploadedFile $file)
    {
        $image = ImageProcessor::make($file);

        // Redimensionnement optimisé
        $image->resize(1200, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Compression et qualité
        $image->encode('jpg', 85);

        // Watermark si configuré
        if (config('artisan.watermark.enabled', false)) {
            $this->ajouterWatermark($image);
        }

        // Optimisation supplémentaire
        $image->sharpen(5);

        return $image;
    }

    /**
     * Ajouter un watermark
     */
    private function ajouterWatermark($image)
    {
        $watermarkText = config('artisan.watermark.text', 'ArtisanConnect');
        $watermarkSize = config('artisan.watermark.size', 16);
        $watermarkOpacity = config('artisan.watermark.opacity', 50);

        $image->text($watermarkText, function ($font) {
            $font->file(public_path('fonts/arial.ttf'));
            $font->size($watermarkSize);
            $font->color('#ffffff');
            $font->align('center', 'bottom');
        })->opacity($watermarkOpacity);
    }

    /**
     * Stocker l'image
     */
    private function stockerImage($processedImage, UploadedFile $originalFile)
    {
        $fileName = time() . '_' . uniqid() . '.jpg';
        $directory = 'oeuvres/' . date('Y/m/d');
        
        $path = $processedImage->store($directory, 'public', [
            'disk' => 'public',
            'filename' => $fileName
        ]);

        return $path;
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

        if ($file->getSize() < 100 * 1024) { // Minimum 100KB
            throw new \Exception('L\'image doit faire au moins 100KB');
        }
    }

    /**
     * Obtenir les informations d'une image
     */
    public function getImageInfo($imageId, $artisanId)
    {
        try {
            $image = Image::with('oeuvre')->findOrFail($imageId);

            // Vérifier que l'image appartient à l'artisan
            if ($image->oeuvre->artisan_id !== $artisanId) {
                return [
                    'success' => false,
                    'message' => 'Cette image ne vous appartient pas'
                ];
            }

            return [
                'success' => true,
                'data' => $image,
                'url' => Storage::url($image->chemin),
                'is_principal' => $image->type === 'principale'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Image non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations de l\'image: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Optimiser une image existante
     */
    public function optimiserImage($imageId, $artisanId)
    {
        try {
            $image = Image::with('oeuvre')->findOrFail($imageId);

            // Vérifier que l'image appartient à l'artisan
            if ($image->oeuvre->artisan_id !== $artisanId) {
                return [
                    'success' => false,
                    'message' => 'Cette image ne vous appartient pas'
                ];
            }

            // Vérifier que le fichier existe
            if (!Storage::disk('public')->exists($image->chemin)) {
                return [
                    'success' => false,
                    'message' => 'Fichier image non trouvé'
                ];
            }

            // Retraiter l'image
            $imagePath = Storage::disk('public')->path($image->chemin);
            $processedImage = ImageProcessor::make($imagePath);
            
            // Optimisation plus agressive
            $processedImage->resize(1200, 1200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $processedImage->encode('jpg', 75); // Qualité réduite pour optimisation
            $processedImage->sharpen(3);

            // Remplacer le fichier
            $processedImage->save($imagePath);

            return [
                'success' => true,
                'message' => 'Image optimisée avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Image non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'optimisation de l\'image: ' . $e->getMessage()
            ];
        }
    }
}
