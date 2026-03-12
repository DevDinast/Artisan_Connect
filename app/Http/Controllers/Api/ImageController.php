<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Oeuvre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * POST /v1/artisan/oeuvres/{id}/images
     * Upload d'images pour une œuvre (RG07 : 3 à 10 images, RG08 : JPG/PNG max 5Mo)
     */
    public function uploadImages(Request $request, $id)
    {
        $artisan = $request->user()->artisan;
        $oeuvre  = Oeuvre::where('artisan_id', $artisan->id)->findOrFail($id);

        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png|max:5120', // RG08 : max 5Mo
        ]);

        // RG07 : vérifier qu'on ne dépasse pas 10 images au total
        $nbExistantes = $oeuvre->images()->count();
        $nbNouvelles  = count($request->file('images'));

        if ($nbExistantes + $nbNouvelles > 10) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => "Limite de 10 images atteinte. Vous avez déjà {$nbExistantes} image(s).",
            ], 422);
        }

        $imagesCreees = [];

        foreach ($request->file('images') as $fichier) {
            $chemin = $fichier->store('oeuvres', 'public');

            // La première image uploadée devient principale s'il n'y en a pas encore
            $isPrincipale = ($nbExistantes === 0 && empty($imagesCreees));

            $image = Image::create([
                'oeuvre_id'     => $oeuvre->id,
                'chemin'        => $chemin,
                'is_principale' => $isPrincipale,
            ]);

            $imagesCreees[] = $image;
            $nbExistantes++;
        }

        return response()->json([
            'success' => true,
            'data'    => ['images' => $imagesCreees],
            'message' => count($imagesCreees) . ' image(s) uploadée(s) avec succès',
        ], 201);
    }

    /**
     * DELETE /v1/artisan/images/{imageId}
     * Suppression d'une image
     */
    public function supprimerImage(Request $request, $imageId)
    {
        $artisan = $request->user()->artisan;

        // Vérifier que l'image appartient bien à une œuvre de cet artisan
        $image = Image::whereHas('oeuvre', function ($q) use ($artisan) {
            $q->where('artisan_id', $artisan->id);
        })->findOrFail($imageId);

        $oeuvre = $image->oeuvre;

        // RG07 : vérifier qu'il restera au moins 3 images après suppression
        // (seulement si l'œuvre est déjà soumise/validée)
        if (in_array($oeuvre->statut, ['en_attente', 'validee'])) {
            $nbImages = $oeuvre->images()->count();
            if ($nbImages <= 3) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Impossible de supprimer : une œuvre doit avoir au moins 3 images.',
                ], 422);
            }
        }

        // Si c'était l'image principale, promouvoir la suivante
        if ($image->is_principale) {
            $prochaine = $oeuvre->images()
                ->where('id', '!=', $imageId)
                ->first();
            if ($prochaine) {
                $prochaine->update(['is_principale' => true]);
            }
        }

        // Supprimer le fichier du storage
        Storage::disk('public')->delete($image->chemin);

        $image->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Image supprimée avec succès',
        ], 200);
    }
}
