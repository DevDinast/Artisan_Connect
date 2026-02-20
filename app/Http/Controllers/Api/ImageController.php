<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Uploader des images pour une œuvre
     */
    public function uploadImages(Request $request, $oeuvreId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            // Vérifier que l'œuvre appartient à l'artisan
            $oeuvre = \App\Models\Oeuvre::where('id', $oeuvreId)
                ->where('artisan_id', $artisan->id)
                ->first();

            if (!$oeuvre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Œuvre non trouvée ou n\'appartient pas à l\'artisan'
                ], 404);
            }

            $images = $request->file('images', []);
            
            if (empty($images)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune image fournie'
                ], 422);
            }

            $result = $this->imageService->traiterMultipleImages($images, $oeuvreId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => count($result['images']) . ' image(s) uploadée(s) avec succès',
                    'data' => $result['images']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload des images',
                    'errors' => $result['results']
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload des images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une image
     */
    public function supprimerImage(Request $request, $imageId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->imageService->supprimerImage($imageId, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réorganiser l'ordre des images
     */
    public function reorganiserImages(Request $request, $oeuvreId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            // Vérifier que l'œuvre appartient à l'artisan
            $oeuvre = \App\Models\Oeuvre::where('id', $oeuvreId)
                ->where('artisan_id', $artisan->id)
                ->first();

            if (!$oeuvre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Œuvre non trouvée ou n\'appartient pas à l\'artisan'
                ], 404);
            }

            $ordreImages = $request->input('ordre', []);
            
            if (empty($ordreImages) || !is_array($ordreImages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordre des images invalide'
                ], 422);
            }

            $result = $this->imageService->reorganiserImages($ordreImages, $oeuvreId, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation des images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Définir une image comme principale
     */
    public function definirImagePrincipale(Request $request, $imageId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->imageService->definirImagePrincipale($imageId, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la définition de l\'image principale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations d'une image
     */
    public function getImageInfo(Request $request, $imageId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->imageService->getImageInfo($imageId, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimiser une image
     */
    public function optimiserImage(Request $request, $imageId)
    {
        try {
            $artisan = $request->user()->artisan;
            
            if (!$artisan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil artisan non trouvé'
                ], 404);
            }

            $result = $this->imageService->optimiserImage($imageId, $artisan->id);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'optimisation de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
