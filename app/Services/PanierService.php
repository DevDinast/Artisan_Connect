<?php

namespace App\Services;

use App\Models\Favori;
use App\Models\Oeuvre;
use Illuminate\Support\Facades\DB;

class PanierService
{
    /**
     * Ajouter une œuvre au panier
     */
    public function ajouterAuPanier($acheteurId, array $data)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($data['oeuvre_id']);

            // Vérifier que l'œuvre est validee et disponible
            if ($oeuvre->statut !== 'validee') {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre n\'est pas disponible à la vente',
                    'statut' => $oeuvre->statut
                ];
            }

            if ($oeuvre->quantite_disponible < $data['quantite']) {
                return [
                    'success' => false,
                    'message' => 'Quantité demandée supérieure à la quantité disponible',
                    'quantite_disponible' => $oeuvre->quantite_disponible
                ];
            }

            // Vérifier si l'article est déjà dans le panier
            $favoriExist = Favori::where('acheteur_id', $acheteurId)
                ->where('oeuvre_id', $data['oeuvre_id'])
                ->where('type', 'panier')
                ->first();

            if ($favoriExist) {
                // Mettre à jour la quantité
                $nouvelleQuantite = $favoriExist->quantite + $data['quantite'];
                
                if ($oeuvre->quantite_disponible < $nouvelleQuantite) {
                    return [
                        'success' => false,
                        'message' => 'Quantité totale supérieure à la quantité disponible',
                        'quantite_disponible' => $oeuvre->quantite_disponible
                    ];
                }

                $favoriExist->update(['quantite' => $nouvelleQuantite]);

                return [
                    'success' => true,
                    'message' => 'Quantité mise à jour dans le panier',
                    'data' => $favoriExist->fresh(['oeuvre'])
                ];
            }

            // Ajouter au panier
            $favori = Favori::create([
                'acheteur_id' => $acheteurId,
                'oeuvre_id' => $data['oeuvre_id'],
                'type' => 'panier',
                'quantite' => $data['quantite'],
            ]);

            return [
                'success' => true,
                'message' => 'Œuvre ajoutée au panier avec succès',
                'data' => $favori->load(['oeuvre'])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir le contenu du panier
     */
    public function getContenuPanier($acheteurId)
    {
        try {
            $panier = Favori::with([
                'oeuvre' => function ($q) {
                    $q->with(['artisan.utilisateur:id,nom,prenom', 'images' => function ($img) {
                        $img->principale()->byOrder();
                    }]);
                }
            ])
            ->where('acheteur_id', $acheteurId)
            ->where('type', 'panier')
            ->latest()
            ->get();

            // Calculer les totaux
            $totalArticles = $panier->sum('quantite');
            $totalPrix = 0;
            $totalCommission = 0;
            $totalArtisans = [];

            foreach ($panier as $item) {
                $sousTotal = $item->quantite * $item->oeuvre->prix;
                $commission = $sousTotal * 0.15; // 15% de commission
                
                $totalPrix += $sousTotal;
                $totalCommission += $commission;
                
                // Regrouper par artisan
                $artisanId = $item->oeuvre->artisan_id;
                if (!isset($totalArtisans[$artisanId])) {
                    $totalArtisans[$artisanId] = [
                        'artisan' => $item->oeuvre->artisan,
                        'total' => 0,
                        'commission' => 0,
                        'articles' => []
                    ];
                }
                $totalArtisans[$artisanId]['total'] += $sousTotal;
                $totalArtisans[$artisanId]['commission'] += $commission;
                $totalArtisans[$artisanId]['articles'][] = $item;
            }

            return [
                'success' => true,
                'data' => $panier,
                'stats' => [
                    'total_articles' => $totalArticles,
                    'total_prix' => $totalPrix,
                    'total_commission' => $totalCommission,
                    'total_avec_commission' => $totalPrix + $totalCommission,
                    'total_artisans' => count($totalArtisans),
                    'details_artisans' => array_values($totalArtisans)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération du panier',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour la quantité d'un article
     */
    public function mettreAJourQuantite($acheteurId, $id, array $data)
    {
        try {
            $favori = Favori::with('oeuvre')
                ->where('id', $id)
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->firstOrFail();

            $nouvelleQuantite = $data['quantite'];

            if ($nouvelleQuantite < 1) {
                return [
                    'success' => false,
                    'message' => 'La quantité doit être supérieure à 0'
                ];
            }

            if ($favori->oeuvre->quantite_disponible < $nouvelleQuantite) {
                return [
                    'success' => false,
                    'message' => 'Quantité demandée supérieure à la quantité disponible',
                    'quantite_disponible' => $favori->oeuvre->quantite_disponible
                ];
            }

            $favori->update(['quantite' => $nouvelleQuantite]);

            return [
                'success' => true,
                'message' => 'Quantité mise à jour avec succès',
                'data' => $favori->fresh(['oeuvre'])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Article non trouvé dans le panier'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la quantité',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un article du panier
     */
    public function supprimerDuPanier($acheteurId, $id)
    {
        try {
            $favori = Favori::where('id', $id)
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->firstOrFail();

            $favori->delete();

            return [
                'success' => true,
                'message' => 'Article supprimé du panier avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Article non trouvé dans le panier'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'article',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vider le panier
     */
    public function viderPanier($acheteurId)
    {
        try {
            $supprimes = Favori::where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->delete();

            return [
                'success' => true,
                'message' => $supprimes . ' article(s) supprimé(s) du panier',
                'articles_supprimes' => $supprimes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du vidage du panier',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques du panier
     */
    public function getStatsPanier($acheteurId)
    {
        try {
            $panier = Favori::with('oeuvre')
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->get();

            $totalArticles = $panier->sum('quantite');
            $totalPrix = 0;
            $totalCommission = 0;

            foreach ($panier as $item) {
                $sousTotal = $item->quantite * $item->oeuvre->prix;
                $totalPrix += $sousTotal;
                $totalCommission += $sousTotal * 0.15;
            }

            return [
                'success' => true,
                'data' => [
                    'nombre_articles' => $panier->count(),
                    'total_articles' => $totalArticles,
                    'total_prix' => $totalPrix,
                    'total_commission' => $totalCommission,
                    'total_avec_commission' => $totalPrix + $totalCommission,
                    'moyenne_prix_article' => $panier->count() > 0 ? $totalPrix / $panier->count() : 0,
                ]
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
     * Vérifier la disponibilité des articles du panier
     */
    public function verifierDisponibilite($acheteurId)
    {
        try {
            $panier = Favori::with('oeuvre')
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'panier')
                ->get();

            $indisponibles = [];
            $quantitesInsuffisantes = [];

            foreach ($panier as $item) {
                if ($item->oeuvre->statut !== 'validee') {
                    $indisponibles[] = [
                        'id' => $item->id,
                        'oeuvre_id' => $item->oeuvre_id,
                        'titre' => $item->oeuvre->titre,
                        'statut' => $item->oeuvre->statut
                    ];
                } elseif ($item->oeuvre->quantite_disponible < $item->quantite) {
                    $quantitesInsuffisantes[] = [
                        'id' => $item->id,
                        'oeuvre_id' => $item->oeuvre_id,
                        'titre' => $item->oeuvre->titre,
                        'quantite_demandee' => $item->quantite,
                        'quantite_disponible' => $item->oeuvre->quantite_disponible
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'panier_valide' => empty($indisponibles) && empty($quantitesInsuffisantes),
                    'indisponibles' => $indisponibles,
                    'quantites_insuffisantes' => $quantitesInsuffisantes
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification de disponibilité',
                'error' => $e->getMessage()
            ];
        }
    }
}
