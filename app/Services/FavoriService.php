<?php

namespace App\Services;

use App\Models\Favori;
use App\Models\Oeuvre;
use Illuminate\Support\Facades\DB;

class FavoriService
{
    /**
     * Ajouter une œuvre aux favoris
     */
    public function ajouterFavori($acheteurId, array $data)
    {
        try {
            $oeuvre = Oeuvre::findOrFail($data['oeuvre_id']);

            // Vérifier que l'œuvre est validée
            if ($oeuvre->statut !== 'validee') {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre n\'est pas disponible aux favoris',
                    'statut' => $oeuvre->statut
                ];
            }

            // Vérifier que l'œuvre n'est pas déjà dans les favoris
            $favoriExistant = Favori::where('acheteur_id', $acheteurId)
                ->where('oeuvre_id', $data['oeuvre_id'])
                ->where('type', 'favori')
                ->first();

            if ($favoriExistant) {
                return [
                    'success' => false,
                    'message' => 'Cette œuvre est déjà dans vos favoris',
                    'favori_id' => $favoriExistant->id
                ];
            }

            // Créer le favori
            $favori = Favori::create([
                'acheteur_id' => $acheteurId,
                'oeuvre_id' => $data['oeuvre_id'],
                'type' => 'favori',
                'quantite' => 1, // Par défaut pour les favoris
            ]);

            return [
                'success' => true,
                'message' => 'Œuvre ajoutée aux favoris avec succès',
                'data' => $favori->load(['oeuvre' => function ($q) {
                    $q->with(['artisan.utilisateur:id,name', 'images' => function ($img) {
                        $img->principale()->byOrder();
                    }]);
                }])
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Œuvre non trouvée'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout aux favoris',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les favoris d'un acheteur
     */
    public function getFavoris($acheteurId)
    {
        try {
            $favoris = Favori::with([
                'oeuvre' => function ($q) {
                    $q->with([
                        'artisan.utilisateur:id,name',
                        'categorie:id,nom,slug',
                        'images' => function ($img) {
                            $img->principale()->byOrder();
                        }
                    ]);
                }
            ])
            ->where('acheteur_id', $acheteurId)
            ->where('type', 'favori')
            ->latest()
            ->paginate(20);

            $stats = $this->getStatistiquesFavoris($acheteurId);

            return [
                'success' => true,
                'data' => $favoris->items(),
                'pagination' => [
                    'current_page' => $favoris->currentPage(),
                    'per_page' => $favoris->perPage(),
                    'total' => $favoris->total(),
                    'last_page' => $favoris->lastPage(),
                ],
                'stats' => $stats['data']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un favori
     */
    public function supprimerFavori($favoriId, $acheteurId)
    {
        try {
            $favori = Favori::where('id', $favoriId)
                ->where('acheteur_id', $acheteurId)
                ->where('type', 'favori')
                ->firstOrFail();

            $favori->delete();

            return [
                'success' => true,
                'message' => 'Favori supprimé avec succès'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Favori non trouvé'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du favori',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier si une œuvre est dans les favoris
     */
    public function verifierFavori($oeuvreId, $acheteurId)
    {
        try {
            $favori = Favori::where('acheteur_id', $acheteurId)
                ->where('oeuvre_id', $oeuvreId)
                ->where('type', 'favori')
                ->first();

            return [
                'success' => true,
                'data' => [
                    'est_favori' => $favori !== null,
                    'favori_id' => $favori ? $favori->id : null,
                    'date_ajout' => $favori ? $favori->created_at : null,
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification des favoris',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des favoris
     */
    public function getStatistiquesFavoris($acheteurId)
    {
        try {
            $stats = [
                'total_favoris' => Favori::where('acheteur_id', $acheteurId)
                    ->where('type', 'favori')
                    ->count(),
                'ajoutes_ce_mois' => Favori::where('acheteur_id', $acheteurId)
                    ->where('type', 'favori')
                    ->whereMonth('created_at', now()->month)
                    ->count(),
                'categories_preferees' => $this->getCategoriesPreferees($acheteurId),
                'prix_moyen_favoris' => $this->getPrixMoyenFavoris($acheteurId),
                'artisans_favoris' => $this->getArtisansFavoris($acheteurId),
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
     * Obtenir les favoris par catégorie
     */
    public function getFavorisParCategorie($acheteurId, $categorieId)
    {
        try {
            $favoris = Favori::with([
                'oeuvre' => function ($q) {
                    $q->with([
                        'artisan.utilisateur:id,name',
                        'images' => function ($img) {
                            $img->principale()->byOrder();
                        }
                    ]);
                }
            ])
            ->where('acheteur_id', $acheteurId)
            ->where('type', 'favori')
            ->whereHas('oeuvre', function ($q) use ($categorieId) {
                $q->where('categorie_id', $categorieId);
            })
            ->latest()
            ->paginate(20);

            return [
                'success' => true,
                'data' => $favoris->items(),
                'pagination' => [
                    'current_page' => $favoris->currentPage(),
                    'per_page' => $favoris->perPage(),
                    'total' => $favoris->total(),
                    'last_page' => $favoris->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris par catégorie',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les favoris récents
     */
    public function getFavorisRecents($acheteurId)
    {
        try {
            $favoris = Favori::with([
                'oeuvre' => function ($q) {
                    $q->with([
                        'artisan.utilisateur:id,name',
                        'images' => function ($img) {
                            $img->principale()->byOrder();
                        }
                    ]);
                }
            ])
            ->where('acheteur_id', $acheteurId)
            ->where('type', 'favori')
            ->latest()
            ->take(10)
            ->get();

            return [
                'success' => true,
                'data' => $favoris
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris récents',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les catégories préférées
     */
    private function getCategoriesPreferees($acheteurId)
    {
        try {
            $categories = Favori::join('oeuvres', 'favoris.oeuvre_id', '=', 'oeuvres.id')
                ->join('categories', 'oeuvres.categorie_id', '=', 'categories.id')
                ->where('favoris.acheteur_id', $acheteurId)
                ->where('favoris.type', 'favori')
                ->select('categories.id', 'categories.nom', 'categories.slug', DB::raw('COUNT(*) as count'))
                ->groupBy('categories.id', 'categories.nom', 'categories.slug')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get();

            return $categories->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtenir le prix moyen des favoris
     */
    private function getPrixMoyenFavoris($acheteurId)
    {
        try {
            $prixMoyen = Favori::join('oeuvres', 'favoris.oeuvre_id', '=', 'oeuvres.id')
                ->where('favoris.acheteur_id', $acheteurId)
                ->where('favoris.type', 'favori')
                ->avg('oeuvres.prix');

            return $prixMoyen ? round($prixMoyen, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtenir les artisans favoris
     */
    private function getArtisansFavoris($acheteurId)
    {
        try {
            $artisans = Favori::join('oeuvres', 'favoris.oeuvre_id', '=', 'oeuvres.id')
                ->join('artisans', 'oeuvres.artisan_id', '=', 'artisans.id')
                ->join('users', 'artisans.user_id', '=', 'users.id')
                ->where('favoris.acheteur_id', $acheteurId)
                ->where('favoris.type', 'favori')
                ->select('artisans.id', 'users.name', DB::raw('COUNT(*) as count'))
                ->groupBy('artisans.id', 'users.name')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get();

            return $artisans->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Nettoyer les anciens favoris (non utilisés depuis longtemps)
     */
    public function nettoyerAnciensFavoris($jours = 365)
    {
        try {
            $supprimes = Favori::where('created_at', '<', now()->subDays($jours))
                ->where('type', 'favori')
                ->delete();

            return [
                'success' => true,
                'message' => $supprimes . ' anciens favoris supprimés',
                'favoris_supprimes' => $supprimes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du nettoyage des favoris',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Synchroniser les favoris (mettre à jour les œuvres qui ne sont plus validées)
     */
    public function synchroniserFavoris()
    {
        try {
            // Supprimer les favoris des œuvres qui ne sont plus validées
            $supprimes = Favori::join('oeuvres', 'favoris.oeuvre_id', '=', 'oeuvres.id')
                ->where('favoris.type', 'favori')
                ->where('oeuvres.statut', '!=', 'validee')
                ->delete();

            return [
                'success' => true,
                'message' => $supprimes . ' favoris synchronisés',
                'favoris_supprimes' => $supprimes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la synchronisation des favoris',
                'error' => $e->getMessage()
            ];
        }
    }
}
