<?php

namespace App\Services;

use App\Models\Avis;
use App\Models\Oeuvre;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class AvisService
{
    /**
     * Créer un nouvel avis
     */
    public function creerAvis($acheteurId, array $data)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($data['oeuvre_id']);

            // Vérifier que l'acheteur a bien acheté cette œuvre
            $aAchete = Transaction::where('acheteur_id', $acheteurId)
                ->where('oeuvre_id', $data['oeuvre_id'])
                ->where('statut', 'payee')
                ->exists();

            if (!$aAchete) {
                return [
                    'success' => false,
                    'message' => 'Vous ne pouvez noter que les œuvres que vous avez achetées'
                ];
            }

            // Vérifier que l'acheteur n'a pas déjà donné un avis
            $avisExistant = Avis::where('acheteur_id', $acheteurId)
                ->where('oeuvre_id', $data['oeuvre_id'])
                ->first();

            if ($avisExistant) {
                return [
                    'success' => false,
                    'message' => 'Vous avez déjà donné un avis pour cette œuvre',
                    'avis_id' => $avisExistant->id
                ];
            }

            DB::beginTransaction();

            // Créer l'avis
            $avis = Avis::create([
                'acheteur_id' => $acheteurId,
                'oeuvre_id' => $data['oeuvre_id'],
                'artisan_id' => $oeuvre->artisan_id,
                'note' => $data['note'],
                'commentaire' => $data['commentaire'] ?? null,
                'titre_avis' => $data['titre_avis'] ?? null,
                'statut' => 'publie', // Publié automatiquement
            ]);

            // Mettre à jour la note moyenne de l'œuvre
            $this->mettreAJourNoteMoyenneOeuvre($data['oeuvre_id']);

            // Mettre à jour la note moyenne de l'artisan
            $this->mettreAJourNoteMoyenneArtisan($oeuvre->artisan_id);

            // Notifier l'artisan
            $this->notifierArtisan($oeuvre->artisan_id, 'avis', 'Nouvel avis reçu', $oeuvre->titre, $data['note']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Avis créé avec succès',
                'data' => $avis->load(['acheteur.utilisateur:id,nom,prenom', 'oeuvre:id,titre'])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les avis d'une œuvre
     */
    public function getAvisOeuvre($oeuvreId)
    {
        try {
            $avis = Avis::with(['acheteur.utilisateur:id,nom,prenom'])
                ->where('oeuvre_id', $oeuvreId)
                ->where('statut', 'publie')
                ->latest()
                ->paginate(10);

            // Calculer les statistiques
            $stats = $this->calculerStatistiquesAvis($oeuvreId);

            return [
                'success' => true,
                'data' => $avis->items(),
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $avis->currentPage(),
                    'per_page' => $avis->perPage(),
                    'total' => $avis->total(),
                    'last_page' => $avis->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un avis
     */
    public function mettreAJourAvis($avisId, $acheteurId, array $data)
    {
        try {
            $avis = Avis::where('id', $avisId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            DB::beginTransaction();

            // Mettre à jour l'avis
            $avis->update($data);

            // Mettre à jour les notes moyennes
            $this->mettreAJourNoteMoyenneOeuvre($avis->oeuvre_id);
            $this->mettreAJourNoteMoyenneArtisan($avis->artisan_id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Avis mis à jour avec succès',
                'data' => $avis->fresh(['acheteur.utilisateur:id,nom,prenom', 'oeuvre:id,titre'])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un avis
     */
    public function supprimerAvis($avisId, $acheteurId)
    {
        try {
            $avis = Avis::where('id', $avisId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            $oeuvreId = $avis->oeuvre_id;
            $artisanId = $avis->artisan_id;

            DB::beginTransaction();

            $avis->delete();

            // Mettre à jour les notes moyennes
            $this->mettreAJourNoteMoyenneOeuvre($oeuvreId);
            $this->mettreAJourNoteMoyenneArtisan($artisanId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Avis supprimé avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les avis d'un acheteur
     */
    public function getAvisAcheteur($acheteurId)
    {
        try {
            $avis = Avis::with(['oeuvre' => function ($q) {
                    $q->with(['artisan.utilisateur:id,nom,prenom', 'images' => function ($img) {
                        $img->principale()->byOrder();
                    }]);
                }])
                ->where('acheteur_id', $acheteurId)
                ->latest()
                ->paginate(15);

            return [
                'success' => true,
                'data' => $avis->items(),
                'pagination' => [
                    'current_page' => $avis->currentPage(),
                    'per_page' => $avis->perPage(),
                    'total' => $avis->total(),
                    'last_page' => $avis->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des avis d'un artisan
     */
    public function getStatistiquesAvisArtisan($artisanId)
    {
        try {
            $stats = [
                'total_avis' => Avis::where('artisan_id', $artisanId)->count(),
                'avis_publies' => Avis::where('artisan_id', $artisanId)->where('statut', 'publie')->count(),
                'note_moyenne' => Avis::where('artisan_id', $artisanId)->where('statut', 'publie')->avg('note') ?? 0,
                'distribution_notes' => $this->getDistributionNotes($artisanId),
                'avis_ce_mois' => Avis::where('artisan_id', $artisanId)
                    ->where('statut', 'publie')
                    ->whereMonth('created_at', now()->month)
                    ->count(),
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

    /**
     * Signaler un avis inapproprié
     */
    public function signalerAvis($avisId, $motif)
    {
        try {
            $avis = Avis::findOrFail($avisId);

            $avis->update([
                'signale' => true,
                'motif_signalement' => $motif,
                'date_signalement' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Avis signalé avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du signalement de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculer les statistiques des avis pour une œuvre
     */
    private function calculerStatistiquesAvis($oeuvreId)
    {
        $avis = Avis::where('oeuvre_id', $oeuvreId)->where('statut', 'publie');

        return [
            'total_avis' => $avis->count(),
            'note_moyenne' => $avis->avg('note') ?? 0,
            'distribution_notes' => $this->getDistributionNotesOeuvre($oeuvreId),
            'dernier_avis' => $avis->latest('created_at')->first(),
        ];
    }

    /**
     * Obtenir la distribution des notes pour un artisan
     */
    private function getDistributionNotes($artisanId)
    {
        $distribution = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = Avis::where('artisan_id', $artisanId)
                ->where('statut', 'publie')
                ->where('note', $i)
                ->count();
        }

        return $distribution;
    }

    /**
     * Obtenir la distribution des notes pour une œuvre
     */
    private function getDistributionNotesOeuvre($oeuvreId)
    {
        $distribution = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = Avis::where('oeuvre_id', $oeuvreId)
                ->where('statut', 'publie')
                ->where('note', $i)
                ->count();
        }

        return $distribution;
    }

    /**
     * Mettre à jour la note moyenne d'une œuvre
     */
    private function mettreAJourNoteMoyenneOeuvre($oeuvreId)
    {
        $noteMoyenne = Avis::where('oeuvre_id', $oeuvreId)
            ->where('statut', 'publie')
            ->avg('note');

        Oeuvre::where('id', $oeuvreId)->update([
            'note_moyenne' => $noteMoyenne ?? 0,
        ]);
    }

    /**
     * Mettre à jour la note moyenne d'un artisan
     */
    private function mettreAJourNoteMoyenneArtisan($artisanId)
    {
        $noteMoyenne = Avis::where('artisan_id', $artisanId)
            ->where('statut', 'publie')
            ->avg('note');

        \App\Models\Artisan::where('id', $artisanId)->update([
            'note_moyenne' => $noteMoyenne ?? 0,
        ]);
    }

    /**
     * Notifier un artisan
     */
    private function notifierArtisan($artisanId, $type, $titre, $message, $note = null)
    {
        try {
            $messageComplet = $message . ($note ? " ({$note}/5)" : "");
            
            Notification::create([
                'utilisateur_id' => $artisanId,
                'type' => $type,
                'titre' => $titre,
                'message' => $messageComplet,
                'lue' => false,
            ]);
        } catch (\Exception $e) {
            // Erreur silencieuse pour la notification
        }
    }
}
