<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Favori;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CommandeService
{

    /**
     * Créer une nouvelle commande
     */
    public function creerCommande($acheteurId, array $data)
    {
        try {
            // Vérifier que le panier n'est pas vide
            $panier = Favori::with('oeuvre')
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->get();

            if ($panier->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Le panier est vide'
                ];
            }

            // Vérifier la disponibilité des articles
            foreach ($panier as $item) {
                if ($item->oeuvre->stock < $item->quantite) {
                    return [
                        'success' => false,
                        'message' => 'Stock insuffisant pour ' . $item->oeuvre->titre,
                        'oeuvre_id' => $item->oeuvre->id,
                        'stock_disponible' => $item->oeuvre->stock,
                        'stock_demande' => $item->quantite
                    ];
                }
            }

            DB::beginTransaction();

            // Calculer les totaux
            $totalCommande = 0;
            $totalCommission = 0;

            foreach ($panier as $item) {
                $sousTotal = $item->quantite * $item->oeuvre->prix;
                $commission = $sousTotal * 0.15;

                $totalCommande += $sousTotal;
                $totalCommission += $commission;
            }

            // Créer la commande
            $commande = Commande::create([
                'acheteur_id' => $acheteurId,
                'reference' => $this->genererReference(),
                'statut' => 'en_attente',
                'montant_total' => $totalCommande,
                'commission' => $totalCommission,
                'montant_avec_commission' => $totalCommande + $totalCommission,
                'adresse_livraison' => $data['adresse_livraison'],
                'telephone_livraison' => $data['telephone_livraison'],
                'instructions_livraison' => $data['instructions_livraison'] ?? null,
                'methode_paiement' => $data['methode_paiement'],
            ]);

            // Créer les transactions
            foreach ($panier as $item) {
                // Réduire le stock
                $item->oeuvre->decrement('stock', $item->quantite);

                // Créer la transaction
                $transaction = Transaction::create([
                    'montant_artisan' => $item->quantite * $item->oeuvre->prix * 0.85,
                    'statut' => 'en_attente',
                    'reference' => $this->genererReference()
                ]);
            }

            // Vider le panier
            Favori::where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Commande créée avec succès',
                'data' => $commande->load(['transactions.oeuvre', 'transactions.artisan.utilisateur']),
                'reference' => $commande->reference
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la commande',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les commandes d'un acheteur
     */
    public function getCommandesAcheteur($acheteurId)
    {
        try {
            $commandes = Commande::with([
                'transactions' => function ($q) {
                    $q->with(['oeuvre' => function ($oeuvre) {
                        $oeuvre->with(['artisan.utilisateur:id,name', 'images' => function ($img) {
                            $img->principale()->byOrder();
                        }]);
                    }]);
                }
            ])
            ->where('acheteur_id', $acheteurId)
            ->latest()
            ->paginate(15);

            return [
                'success' => true,
                'data' => $commandes->items(),
                'pagination' => [
                    'current_page' => $commandes->currentPage(),
                    'per_page' => $commandes->perPage(),
                    'total' => $commandes->total(),
                    'last_page' => $commandes->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les détails d'une commande
     */
    public function getDetailCommande($commandeId, $acheteurId)
    {
        try {
            $commande = Commande::with([
                'transactions' => function ($q) {
                    $q->with([
                        'oeuvre' => function ($oeuvre) {
                            $oeuvre->with(['artisan.utilisateur:id,name', 'images' => function ($img) {
                                $img->principale()->byOrder();
                            }]);
                        },
                        'acheteur.utilisateur:id,name'
                    ]);
                }
            ])
            ->where('id', $commandeId)
            ->where('acheteur_id', $acheteurId)
            ->firstOrFail();

            return [
                'success' => true,
                'data' => $commande
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Commande non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la commande',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler une commande
     */
    public function annulerCommande($commandeId, $acheteurId)
    {
        try {
            $commande = Commande::where('id', $commandeId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            // Vérifier que la commande peut être annulée
            if (!in_array($commande->statut, ['en_attente', 'confirmee'])) {
                return [
                    'success' => false,
                    'message' => 'Cette commande ne peut plus être annulée',
                    'statut' => $commande->statut
                ];
            }

            DB::beginTransaction();

            // Mettre à jour le statut de la commande
            $commande->update([
                'statut' => 'annulee',
                'date_annulation' => now(),
            ]);

            // Annuler toutes les transactions
            foreach ($commande->transactions as $transaction) {
                $transaction->update(['statut' => 'annulee']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Commande annulée avec succès',
                'data' => $commande->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Commande non trouvée'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la commande',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirmer la réception d'une commande
     */
    public function confirmerReception($commandeId, $acheteurId)
    {
        try {
            $commande = Commande::where('id', $commandeId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            // Vérifier que la commande peut être confirmée
            if ($commande->statut !== 'livree') {
                return [
                    'success' => false,
                    'message' => 'Cette commande ne peut pas être confirmée',
                    'statut' => $commande->statut
                ];
            }

            $commande->update([
                'statut' => 'terminee',
                'date_reception' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Réception confirmée avec succès',
                'data' => $commande->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Commande non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la confirmation de réception',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Générer une référence de commande unique
     */
    private function genererReference()
    {
        do {
            $reference = 'CMD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Commande::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Obtenir les statistiques des commandes
     */
    public function getStatistiquesCommandes($acheteurId = null)
    {
        try {
            $query = Commande::query();

            if ($acheteurId) {
                $query->where('acheteur_id', $acheteurId);
            }

            $stats = [
                'total_commandes' => $query->count(),
                'commandes_en_attente' => $query->where('statut', 'en_attente')->count(),
                'commandes_confirmees' => $query->where('statut', 'confirmee')->count(),
                'commandes_livrees' => $query->where('statut', 'livree')->count(),
                'commandes_terminees' => $query->where('statut', 'terminee')->count(),
                'commandes_annulees' => $query->where('statut', 'annulee')->count(),
                'montant_total_ventes' => $query->whereIn('statut', ['confirmee', 'livree', 'terminee'])->sum('montant_avec_commission'),
                'total_commission_generee' => $query->whereIn('statut', ['confirmee', 'livree', 'terminee'])->sum('commission'),
                'commandes_ce_mois' => $query->whereMonth('created_at', now()->month)->count(),
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
