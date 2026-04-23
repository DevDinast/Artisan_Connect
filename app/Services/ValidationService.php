<?php

namespace App\Services;

use App\Models\Artisan;
use App\Models\Oeuvre;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class ValidationService
{
    /**
     * Obtenir les œuvres en attente de validation
     */
    public function getOeuvresEnAttente()
    {
        try {
            $oeuvres = Oeuvre::with([
                'artisan.utilisateur:id,name,email',
                'categorie:id,nom,slug',
                'images' => function ($q) {
                    $q->principale()->byOrder();
                }
            ])
            ->where('statut', 'en_attente')
            ->latest()
            ->paginate(20);

            return [
                'success' => true,
                'data' => $oeuvres->items(),
                'pagination' => [
                    'current_page' => $oeuvres->currentPage(),
                    'per_page' => $oeuvres->perPage(),
                    'total' => $oeuvres->total(),
                    'last_page' => $oeuvres->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des œuvres en attente',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valider une œuvre
     */
    public function validerOeuvre($oeuvreId, array $data, $adminId)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($oeuvreId);

            // Vérifier que l'œuvre est en attente
            if ($oeuvre->statut !== 'en_attente') {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre n\'est pas en attente de validation',
                    'statut_actuel' => $oeuvre->statut
                ];
            }

            DB::beginTransaction();

            // Mettre à jour l'œuvre
            $oeuvre->update([
                'statut' => 'validee',
                'date_validation' => now(),
                'validateur_id' => $adminId,
                'motif_refus' => null,
            ]);

            // Notifier l'artisan
            $this->notifierArtisan($oeuvre->artisan_id, 'validation', 'Œuvre validée', $oeuvre->titre);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Œuvre validée avec succès',
                'data' => $oeuvre->fresh()
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
                'message' => 'Erreur lors de la validation de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refuser une œuvre
     */
    public function refuserOeuvre($oeuvreId, array $data, $adminId)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($oeuvreId);

            // Vérifier que l'œuvre peut être refusée
            if (!in_array($oeuvre->statut, ['en_attente', 'validee'])) {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre ne peut pas être refusée',
                    'statut_actuel' => $oeuvre->statut
                ];
            }

            if (empty($data['motif_refus'])) {
                return [
                    'success' => false,
                    'message' => 'Le motif de refus est obligatoire'
                ];
            }

            DB::beginTransaction();

            // Mettre à jour l'œuvre
            $oeuvre->update([
                'statut' => 'refusee',
                'motif_refus' => $data['motif_refus'],
                'date_validation' => null,
                'validateur_id' => $adminId,
            ]);

            // Notifier l'artisan
            $this->notifierArtisan($oeuvre->artisan_id, 'refus', 'Œuvre refusée', $oeuvre->titre, $data['motif_refus']);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Œuvre refusée avec succès',
                'data' => $oeuvre->fresh()
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
                'message' => 'Erreur lors du refus de l\'œuvre',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques de validation
     */
    public function getStatistiquesValidation()
    {
        try {
            $stats = [
                'en_attente' => Oeuvre::where('statut', 'en_attente')->count(),
                'validees_ce_mois' => Oeuvre::where('statut', 'validee')
                    ->whereMonth('date_validation', now()->month)
                    ->count(),
                'refusees_ce_mois' => Oeuvre::where('statut', 'refusee')
                    ->whereMonth('date_validation', now()->month)
                    ->count(),
                'total_validees' => Oeuvre::where('statut', 'validee')->count(),
                'total_refusees' => Oeuvre::where('statut', 'refusee')->count(),
                'temps_moyen_validation' => DB::table('oeuvres')
                    ->where('statut', 'validee')
                    ->select(DB::raw('AVG(DATEDIFF(created_at, date_validation)) as temps_moyen'))
                    ->value('temps_moyen') ?? 0,
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
     * Obtenir l'historique des validations
     */
    public function getHistoriqueValidations()
    {
        try {
            $validations = Oeuvre::with([
                'artisan.utilisateur:id,name',
                'validateur.utilisateur:id,name',
                'categorie:id,nom,slug'
            ])
            ->whereIn('statut', ['validee', 'refusee'])
            ->latest('date_validation')
            ->paginate(20);

            return [
                'success' => true,
                'data' => $validations->items(),
                'pagination' => [
                    'current_page' => $validations->currentPage(),
                    'per_page' => $validations->perPage(),
                    'total' => $validations->total(),
                    'last_page' => $validations->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Notifier un artisan
     */
    private function notifierArtisan($artisanId, $type, $titre, $message, $details = null)
    {
        try {
            $artisan = Artisan::find($artisanId);

            if (!$artisan || !$artisan->user_id) {
                return;
            }

            Notification::create([
                'user_id' => $artisan->user_id,
                'type' => $type,
                'titre' => $titre,
                'message' => $message,
                'lue' => false,
            ]);
        } catch (\Exception $e) {
            // Erreur silencieuse pour la notification
        }
    }
}
