<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Oeuvre;
use App\Models\Artisan;
use App\Models\Acheteur;
use App\Models\Panier;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Calculer les montants d'une transaction
     * RG19 : commission 15% standard, 10% pour artisans certifiés
     * RG20 : certifié = 50 ventes ET note > 4/5
     */
    public function calculerMontants(Oeuvre $oeuvre, int $quantite): array
    {
        $montantTotal = $oeuvre->prix * $quantite;

        // RG20 : vérifier si l'artisan est certifié
        $artisan     = $oeuvre->artisan;
        $estCertifie = $artisan->nb_ventes >= 50 && $artisan->note_moyenne > 4;

        // RG19 : taux de commission selon certification
        $taux       = $estCertifie ? 0.10 : 0.15;
        $commission = round($montantTotal * $taux, 2);
        $montantArtisan = round($montantTotal - $commission, 2);

        return [
            'montant_total'         => $montantTotal,
            'commission_plateforme' => $commission,
            'montant_artisan'       => $montantArtisan,
            'taux_commission'       => $taux,
            'artisan_certifie'      => $estCertifie,
        ];
    }

    /**
     * Créer une commande depuis le panier ou directement
     */
    public function creerCommande(Acheteur $acheteur, Oeuvre $oeuvre, array $data): array
    {
        $quantite = $data['quantite'] ?? 1;

        // Vérifier le stock
        if ($oeuvre->stock < $quantite) {
            return ['success' => false, 'message' => 'Stock insuffisant.'];
        }

        DB::beginTransaction();
        try {
            $montants = $this->calculerMontants($oeuvre, $quantite);

            $transaction = Transaction::create([
                'acheteur_id'           => $acheteur->id,
                'oeuvre_id'             => $oeuvre->id,
                'montant_total'         => $montants['montant_total'],
                'commission_plateforme' => $montants['commission_plateforme'],
                'montant_artisan'       => $montants['montant_artisan'],
                'statut'                => 'en_attente',
                'mode_paiement'         => $data['mode_paiement'],
                'adresse_livraison'     => json_encode($data['adresse_livraison']),
                'frais_livraison'       => $data['frais_livraison'] ?? 0,
            ]);

            // RG17 : décrémentation stock
            $oeuvre->decrement('stock', $quantite);

            // RG18 : statut épuisée si stock = 0
            if ($oeuvre->fresh()->stock <= 0) {
                $oeuvre->update(['statut' => 'epuisee']);
            }

            // Vider le panier pour cette œuvre
            Panier::where('acheteur_id', $acheteur->id)
                  ->where('oeuvre_id', $oeuvre->id)
                  ->delete();

            DB::commit();

            return ['success' => true, 'transaction' => $transaction];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Erreur lors de la création de la commande.'];
        }
    }

    /**
     * Workflow post-paiement confirmé (appelé depuis webhook Mobile Money)
     * RG17 : stock déjà décrémenté à la commande
     */
    public function confirmerPaiement(Transaction $transaction, string $reference): array
    {
        DB::beginTransaction();
        try {
            $transaction->update([
                'statut'              => 'payee',
                'reference_paiement'  => $reference,
                'date_paiement'       => now(),
            ]);

            $oeuvre  = $transaction->oeuvre;
            $artisan = $oeuvre->artisan;

            // Incrémenter les ventes de l'artisan
            $artisan->increment('nb_ventes');

            // Notifier l'artisan
            Notification::create([
                'user_id' => $artisan->user_id,
                'type'    => 'paiement_recu',
                'titre'   => 'Paiement reçu !',
                'message' => "Paiement confirmé pour \"{$oeuvre->titre}\". Montant : {$transaction->montant_artisan} FCFA.",
                'lue'     => false,
            ]);

            // Notifier l'acheteur
            Notification::create([
                'user_id' => $transaction->acheteur->user_id,
                'type'    => 'commande_confirmee',
                'titre'   => 'Commande confirmée !',
                'message' => "Votre commande pour \"{$oeuvre->titre}\" a été confirmée.",
                'lue'     => false,
            ]);

            DB::commit();

            return ['success' => true, 'transaction' => $transaction->fresh()];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Erreur lors de la confirmation du paiement.'];
        }
    }
}
