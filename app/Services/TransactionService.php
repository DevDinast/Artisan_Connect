<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Oeuvre;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    private const COMMISSION_RATE = 0.15; // 15%

    /**
     * Créer des transactions pour une commande
     */
    public function creerTransactions($commandeId, $articles)
    {
        try {
            $transactions = [];
            $totalCommission = 0;

            DB::beginTransaction();

            foreach ($articles as $article) {
                $oeuvre = $article->oeuvre;
                $quantite = $article->quantite;
                $prixUnitaire = $oeuvre->prix;
                $sousTotal = $quantite * $prixUnitaire;
                $commission = $sousTotal * self::COMMISSION_RATE;
                $montantArtisan = $sousTotal - $commission;

                // Mettre à jour la quantité disponible
                $oeuvre->decrement('quantite_disponible', $quantite);

                // Créer la transaction
                $transaction = Transaction::create([
                    'commande_id' => $commandeId,
                    'acheteur_id' => $article->acheteur_id,
                    'oeuvre_id' => $oeuvre->id,
                    'artisan_id' => $oeuvre->artisan_id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'montant_total' => $sousTotal,
                    'commission' => $commission,
                    'montant_artisan' => $montantArtisan,
                    'statut' => 'en_attente',
                ]);

                $transactions[] = $transaction;
                $totalCommission += $commission;

                // Notifier l'artisan
                $this->notifierArtisan($oeuvre->artisan_id, 'vente', 'Nouvelle vente', $oeuvre->titre, $quantite, $montantArtisan);
            }

            DB::commit();

            return [
                'success' => true,
                'transactions' => $transactions,
                'total_commission' => $totalCommission,
                'message' => count($transactions) . ' transaction(s) créée(s) avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la création des transactions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour le statut d'une transaction
     */
    public function mettreAJourStatut($transactionId, $statut, $motif = null)
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);

            $statutsAutorises = ['en_attente', 'payee', 'annulee', 'remboursee'];
            if (!in_array($statut, $statutsAutorises)) {
                return [
                    'success' => false,
                    'message' => 'Statut non autorisé',
                    'statuts_autorises' => $statutsAutorises
                ];
            }

            $transaction->update([
                'statut' => $statut,
                'date_paiement' => $statut === 'payee' ? now() : null,
                'motif_annulation' => $motif,
            ]);

            // Notifier selon le statut
            if ($statut === 'payee') {
                $this->notifierArtisan($transaction->artisan_id, 'paiement', 'Paiement reçu', $transaction->oeuvre->titre, $transaction->montant_artisan);
                $this->notifierAcheteur($transaction->acheteur_id, 'paiement', 'Paiement confirmé', $transaction->oeuvre->titre);
            } elseif ($statut === 'annulee') {
                // Remettre la quantité en stock
                $transaction->oeuvre->increment('quantite_disponible', $transaction->quantite);
                $this->notifierArtisan($transaction->artisan_id, 'annulation', 'Commande annulée', $transaction->oeuvre->titre);
            }

            return [
                'success' => true,
                'message' => 'Statut de transaction mis à jour avec succès',
                'data' => $transaction->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Transaction non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les transactions d'une commande
     */
    public function getTransactionsCommande($commandeId, $acheteurId)
    {
        try {
            $transactions = Transaction::with([
                'oeuvre' => function ($q) {
                    $q->with(['artisan.utilisateur:id,nom,prenom', 'images' => function ($img) {
                        $img->principale()->byOrder();
                    }]);
                }
            ])
            ->where('commande_id', $commandeId)
            ->where('acheteur_id', $acheteurId)
            ->get();

            if ($transactions->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucune transaction trouvée pour cette commande'
                ];
            }

            // Calculer les totaux
            $totalCommande = $transactions->sum('montant_total');
            $totalCommission = $transactions->sum('commission');
            $totalArtisans = $transactions->sum('montant_artisan');

            return [
                'success' => true,
                'data' => $transactions,
                'stats' => [
                    'total_commande' => $totalCommande,
                    'total_commission' => $totalCommission,
                    'total_artisans' => $totalArtisans,
                    'nombre_transactions' => $transactions->count(),
                    'taux_commission' => self::COMMISSION_RATE * 100 . '%'
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des transactions
     */
    public function getStatistiquesTransactions($artisanId = null)
    {
        try {
            $query = Transaction::query();
            
            if ($artisanId) {
                $query->where('artisan_id', $artisanId);
            }

            $stats = [
                'total_transactions' => $query->count(),
                'total_ventes' => $query->where('statut', 'payee')->count(),
                'total_en_attente' => $query->where('statut', 'en_attente')->count(),
                'total_annulees' => $query->where('statut', 'annulee')->count(),
                'montant_total_ventes' => $query->where('statut', 'payee')->sum('montant_total'),
                'total_commission_generee' => $query->where('statut', 'payee')->sum('commission'),
                'revenus_artisans' => $query->where('statut', 'payee')->sum('montant_artisan'),
                'moyenne_par_transaction' => $query->where('statut', 'payee')->avg('montant_total'),
                'ventes_ce_mois' => $query->where('statut', 'payee')->whereMonth('created_at', now()->month)->count(),
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
     * Obtenir les transactions d'un artisan
     */
    public function getTransactionsArtisan($artisanId)
    {
        try {
            $transactions = Transaction::with([
                'acheteur.utilisateur:id,nom,prenom,email',
                'oeuvre' => function ($q) {
                    $q->with(['images' => function ($img) {
                        $img->principale()->byOrder();
                    }]);
                }
            ])
            ->where('artisan_id', $artisanId)
            ->latest()
            ->paginate(20);

            return [
                'success' => true,
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculer la commission pour un montant
     */
    public function calculerCommission($montant)
    {
        return [
            'montant' => $montant,
            'commission' => $montant * self::COMMISSION_RATE,
            'montant_net' => $montant - ($montant * self::COMMISSION_RATE),
            'taux_commission' => self::COMMISSION_RATE * 100 . '%'
        ];
    }

    /**
     * Notifier un artisan
     */
    private function notifierArtisan($artisanId, $type, $titre, $message, $details = null)
    {
        try {
            Notification::create([
                'utilisateur_id' => $artisanId,
                'type' => $type,
                'titre' => $titre,
                'message' => $message,
                'lue' => false,
            ]);
        } catch (\Exception $e) {
            // Erreur silencieuse pour la notification
        }
    }

    /**
     * Notifier un acheteur
     */
    private function notifierAcheteur($acheteurId, $type, $titre, $message)
    {
        try {
            Notification::create([
                'utilisateur_id' => $acheteurId,
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
