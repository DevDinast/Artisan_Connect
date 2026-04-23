<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    /**
     * Initier un paiement Mobile Money
     */
    public function initierPaiement($acheteurId, array $data)
    {
        try {
            $commande = Commande::with(['transactions'])
                ->where('id', $data['commande_id'])
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            // Vérifier que la commande peut être payée
            if ($commande->statut !== 'en_attente') {
                return [
                    'success' => false,
                    'message' => 'Cette commande ne peut plus être payée',
                    'statut' => $commande->statut
                ];
            }

            // Vérifier qu'il n'y a pas déjà un paiement en cours
            $paiementExistant = Transaction::where('commande_id', $commande->id)
                ->whereIn('statut', ['en_attente', 'en_cours'])
                ->first();

            if ($paiementExistant) {
                return [
                    'success' => false,
                    'message' => 'Un paiement est déjà en cours pour cette commande',
                    'paiement_id' => $paiementExistant->id
                ];
            }

            DB::beginTransaction();

            // Créer le paiement via Transaction
            $paiement = Transaction::create([
                'commande_id' => $commande->id,
                'acheteur_id' => $acheteurId,
                'montant_total' => $commande->montant_avec_commission,
                'mode_paiement' => $data['methode'],
                'statut' => 'en_attente',
                'reference' => $this->genererReferencePaiement(),
            ]);

            // Mettre à jour le statut de la commande
            $commande->update(['statut' => 'en_cours_paiement']);

            DB::commit();

            // Simuler l'initiation du paiement Mobile Money
            $resultatInitiation = $this->simulerInitiationMobileMoney($paiement);

            return [
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'data' => [
                    'paiement' => $paiement,
                    'reference' => $paiement->reference,
                    'montant' => $paiement->montant_total,
                ]
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Commande non trouvée'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function getStatutPaiement($paiementId, $acheteurId)
    {
        try {
            $paiement = Transaction::where('id', $paiementId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            // Vérifier si le paiement a expiré
            if ($paiement->statut === 'en_attente' && $paiement->created_at->addMinutes(15) < now()) {
                $paiement->update([
                    'statut' => 'expire'
                ]);

                // Remettre la commande en attente
                if ($paiement->commande) {
                    $paiement->commande->update(['statut' => 'en_attente']);
                }
            }

            return [
                'success' => true,
                'data' => $paiement->fresh(),
                'statut' => $paiement->fresh()->statut
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Paiement non trouvé'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut du paiement',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirmer un paiement (callback Mobile Money)
     */
    public function confirmerPaiement($paiementId, array $data)
    {
        try {
            $paiement = Transaction::findOrFail($paiementId);

            if ($paiement->statut !== 'en_attente') {
                return [
                    'success' => false,
                    'message' => 'Ce paiement ne peut plus être confirmé',
                    'statut' => $paiement->statut
                ];
            }

            DB::beginTransaction();

            // Mettre à jour le paiement/transaction
            $paiement->update([
                'statut' => 'payee',
            ]);

            // Mettre à jour la commande
            if ($paiement->commande) {
                $paiement->commande->update(['statut' => 'confirmee']);
            }

            // Notifier l'acheteur
            $this->notifierUtilisateur($paiement->acheteur_id, 'paiement', 'Paiement confirmé', 'Votre paiement a été traité avec succès');

            // Notifier l'artisan
            if ($paiement->artisan_id) {
                $this->notifierUtilisateur($paiement->artisan_id, 'vente', 'Nouvelle vente', 'Une commande a été payée');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Paiement confirmé avec succès',
                'data' => $paiement->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Paiement non trouvé'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler un paiement
     */
    public function annulerPaiement($paiementId, $acheteurId)
    {
        try {
            $paiement = Transaction::where('id', $paiementId)
                ->where('acheteur_id', $acheteurId)
                ->firstOrFail();

            if (!in_array($paiement->statut, ['en_attente', 'en_cours'])) {
                return [
                    'success' => false,
                    'message' => 'Ce paiement ne peut plus être annulé',
                    'statut' => $paiement->statut
                ];
            }

            DB::beginTransaction();

            // Mettre à jour le paiement
            $paiement->update([
                'statut' => 'annulee',
            ]);

            // Remettre la commande en attente
            if ($paiement->commande) {
                $paiement->commande->update(['statut' => 'en_attente']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Paiement annulé avec succès',
                'data' => $paiement->fresh()
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Paiement non trouvé'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du paiement',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir l'historique des paiements
     */
    public function getHistoriquePaiements($acheteurId)
    {
        try {
            $paiements = Transaction::where('acheteur_id', $acheteurId)
                ->latest()
                ->paginate(15);

            return [
                'success' => true,
                'data' => $paiements->items(),
                'pagination' => [
                    'current_page' => $paiements->currentPage(),
                    'per_page' => $paiements->perPage(),
                    'total' => $paiements->total(),
                    'last_page' => $paiements->lastPage(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique des paiements',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getMethodesPaiement()
    {
        try {
            $methodes = [
                [
                    'code' => 'orange_money',
                    'nom' => 'Orange Money',
                    'icone' => 'orange',
                    'description' => 'Paiement via Orange Money',
                    'frais' => 0,
                    'disponible' => true,
                ],
                [
                    'code' => 'mtn_money',
                    'nom' => 'MTN Mobile Money',
                    'icone' => 'mtn',
                    'description' => 'Paiement via MTN Mobile Money',
                    'frais' => 0,
                    'disponible' => true,
                ],
                [
                    'code' => 'moov_money',
                    'nom' => 'Moov Money',
                    'icone' => 'moov',
                    'description' => 'Paiement via Moov Money',
                    'frais' => 0,
                    'disponible' => true,
                ],
                [
                    'code' => 'wave',
                    'nom' => 'Wave',
                    'icone' => 'wave',
                    'description' => 'Paiement via Wave',
                    'frais' => 0,
                    'disponible' => true,
                ],
            ];

            return [
                'success' => true,
                'data' => $methodes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des méthodes de paiement',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Simuler l'initiation du paiement Mobile Money
     */
    private function simulerInitiationMobileMoney($paiement)
    {
        $instructions = [
            'orange_money' => [
                'etape_1' => 'Composer #144#',
                'etape_2' => 'Choisir "Paiement marchand"',
                'etape_3' => 'Entrer le numéro du marchand: 123456',
                'etape_4' => 'Entrer le montant: ' . $paiement->montant . ' FCFA',
                'etape_5' => 'Entrer votre code secret pour valider',
                'reference' => $paiement->reference,
            ],
            'mtn_money' => [
                'etape_1' => 'Composer #133#',
                'etape_2' => 'Choisir "Paiement"',
                'etape_3' => 'Entrer le montant: ' . $paiement->montant . ' FCFA',
                'etape_4' => 'Entrer la référence: ' . $paiement->reference,
                'etape_5' => 'Entrer votre code secret pour valider',
            ],
            'moov_money' => [
                'etape_1' => 'Composer #155#',
                'etape_2' => 'Choisir "Paiement marchand"',
                'etape_3' => 'Entrer la référence: ' . $paiement->reference,
                'etape_4' => 'Confirmer le montant: ' . $paiement->montant . ' FCFA',
                'etape_5' => 'Valider avec votre code secret',
            ],
            'wave' => [
                'etape_1' => 'Ouvrir l\'application Wave',
                'etape_2' => 'Scanner le QR code ou entrer la référence',
                'etape_3' => 'Confirmer le montant: ' . $paiement->montant . ' FCFA',
                'etape_4' => 'Valider le paiement',
                'reference' => $paiement->reference,
            ],
        ];

        return [
            'instructions' => $instructions[$paiement->methode] ?? $instructions['orange_money'],
            'delai' => '15 minutes',
            'support' => 'Contactez le support en cas de problème'
        ];
    }

    /**
     * Obtenir l'opérateur en fonction de la méthode
     */
    private function getOperateur($methode)
    {
        $operateurs = [
            'orange_money' => 'Orange',
            'mtn_money' => 'MTN',
            'moov_money' => 'Moov',
            'wave' => 'Wave',
        ];

        return $operateurs[$methode] ?? 'Inconnu';
    }

    /**
     * Générer une référence de paiement unique
     */
    private function genererReferencePaiement()
    {
        do {
            $reference = 'PAY' . date('YmdHis') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Notifier un utilisateur
     */
    private function notifierUtilisateur($utilisateurId, $type, $titre, $message)
    {
        try {
            Notification::create([
                'user_id' => $utilisateurId,
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
