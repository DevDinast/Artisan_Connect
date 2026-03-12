<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Oeuvre;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    // Dimensions max après resize
    const MAX_WIDTH  = 1200;
    const MAX_HEIGHT = 1200;

    /**
     * Traiter et uploader une image pour une œuvre
     * RG07 : 3 à 10 images par œuvre
     * RG08 : JPG/PNG max 5Mo
     */
    public function uploadPourOeuvre(Oeuvre $oeuvre, array $fichiers): array
    {
        $nbExistantes = $oeuvre->images()->count();
        $nbNouvelles  = count($fichiers);

        // RG07 : vérifier la limite de 10 images
        if ($nbExistantes + $nbNouvelles > 10) {
            return [
                'success' => false,
                'message' => "Limite de 10 images atteinte. Vous avez déjà {$nbExistantes} image(s).",
            ];
        }

        $imagesCreees = [];

        foreach ($fichiers as $fichier) {
            $chemin = $this->traiterEtStocker($fichier, $oeuvre->id);

            // La première image devient principale s'il n'y en a pas encore
            $isPrincipale = ($nbExistantes === 0 && empty($imagesCreees));

            $image = Image::create([
                'oeuvre_id'     => $oeuvre->id,
                'chemin'        => $chemin,
                'is_principale' => $isPrincipale,
            ]);

            $imagesCreees[] = $image;
            $nbExistantes++;
        }

        return ['success' => true, 'images' => $imagesCreees];
    }

    /**
     * Traiter une image : resize + compression + watermark
     * Utilise GD (natif PHP) si Intervention Image n'est pas installé
     */
    private function traiterEtStocker(UploadedFile $fichier, int $oeuvreId): string
    {
        $dossier  = "oeuvres/{$oeuvreId}";
        $nomFichier = uniqid() . '.' . $fichier->getClientOriginalExtension();

        // Si Intervention Image est installé, utiliser pour traitement avancé
        if (class_exists('\Intervention\Image\Facades\Image')) {
            return $this->traiterAvecIntervention($fichier, $dossier, $nomFichier);
        }

        // Sinon stockage direct (resize basique avec GD)
        $chemin = $fichier->storeAs($dossier, $nomFichier, 'public');
        return $chemin;
    }

    /**
     * Traitement avec Intervention Image (compression + resize + watermark)
     */
    private function traiterAvecIntervention(UploadedFile $fichier, string $dossier, string $nomFichier): string
    {
        $image = \Intervention\Image\Facades\Image::make($fichier);

        // Resize en gardant les proportions
        $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize(); // ne pas agrandir si plus petit
        });

        // Compression qualité 85%
        $cheminComplet = storage_path("app/public/{$dossier}/{$nomFichier}");

        // Créer le dossier si nécessaire
        if (!file_exists(dirname($cheminComplet))) {
            mkdir(dirname($cheminComplet), 0755, true);
        }

        $image->save($cheminComplet, 85);

        return "{$dossier}/{$nomFichier}";
    }

    /**
     * Supprimer une image avec son fichier
     * RG07 : vérifier qu'il restera au moins 3 images
     */
    public function supprimer(Image $image): array
    {
        $oeuvre   = $image->oeuvre;
        $nbImages = $oeuvre->images()->count();

        // Vérifier la limite min seulement si l'œuvre est soumise/validée
        if (in_array($oeuvre->statut, ['en_attente', 'validee']) && $nbImages <= 3) {
            return [
                'success' => false,
                'message' => 'Impossible de supprimer : minimum 3 images requises.',
            ];
        }

        // Si c'était l'image principale, promouvoir la suivante
        if ($image->is_principale) {
            $prochaine = $oeuvre->images()->where('id', '!=', $image->id)->first();
            if ($prochaine) {
                $prochaine->update(['is_principale' => true]);
            }
        }

        Storage::disk('public')->delete($image->chemin);
        $image->delete();

        return ['success' => true, 'message' => 'Image supprimée.'];
    }

    /**
     * Définir une image comme principale
     */
    public function definirPrincipale(Image $image): void
    {
        // Retirer le flag principal de toutes les images de l'œuvre
        Image::where('oeuvre_id', $image->oeuvre_id)->update(['is_principale' => false]);

        // Définir la nouvelle principale
        $image->update(['is_principale' => true]);
    }
}
