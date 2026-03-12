<?php

namespace App\Services;

use App\Models\Oeuvre;
use App\Models\Artisan;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class OeuvreService
{
    /**
     * Soumettre une œuvre pour validation (brouillon → en_attente)
     * RG06 : max 50 œuvres en attente simultanément
     */
    public function soumettre(Oeuvre $oeuvre, Artisan $artisan): array
    {
        // Vérifier que l'œuvre appartient à cet artisan
        if ($oeuvre->artisan_id !== $artisan->id) {
            return ['success' => false, 'message' => 'Accès non autorisé.'];
        }

        // Vérifier le statut actuel
        if (!in_array($oeuvre->statut, ['brouillon', 'refusee', 'modif_requise'])) {
            return ['success' => false, 'message' => 'Cette œuvre ne peut pas être soumise dans son état actuel.'];
        }

        // RG06 : max 50 œuvres en attente
        $nbEnAttente = Oeuvre::where('artisan_id', $artisan->id)
            ->where('statut', 'en_attente')
            ->count();

        if ($nbEnAttente >= 50) {
            return ['success' => false, 'message' => 'Limite de 50 œuvres en attente atteinte.'];
        }

        // RG07 : vérifier qu'il y a entre 3 et 10 images
        $nbImages = $oeuvre->images()->count();
        if ($nbImages < 3) {
            return ['success' => false, 'message' => "L'œuvre doit avoir au moins 3 images ({$nbImages} actuellement)."];
        }

        // RG09 : vérifier les champs obligatoires
        $champsObligatoires = ['titre', 'description', 'categorie_id', 'prix', 'stock'];
        foreach ($champsObligatoires as $champ) {
            if (empty($oeuvre->$champ)) {
                return ['success' => false, 'message' => "Le champ {$champ} est obligatoire."];
            }
        }

        $oeuvre->update(['statut' => 'en_attente']);

        // Notifier les admins
        $this->notifierAdmins($oeuvre);

        return ['success' => true, 'message' => 'Œuvre soumise pour validation.', 'oeuvre' => $oeuvre->fresh()];
    }

    /**
     * Notifier tous les administrateurs d'une nouvelle soumission
     */
    private function notifierAdmins(Oeuvre $oeuvre): void
    {
        $admins = \App\Models\Administrateur::with('user')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->user_id,
                'type'    => 'nouvelle_soumission',
                'titre'   => 'Nouvelle œuvre en attente',
                'message' => "L'artisan a soumis \"{$oeuvre->titre}\" pour validation.",
                'lue'     => false,
            ]);
        }
    }

    /**
     * Soft delete d'une œuvre avec nettoyage des images
     */
    public function supprimer(Oeuvre $oeuvre): void
    {
        // Supprimer les fichiers images du storage
        foreach ($oeuvre->images as $image) {
            Storage::disk('public')->delete($image->chemin);
            $image->delete();
        }

        $oeuvre->delete();
    }
}