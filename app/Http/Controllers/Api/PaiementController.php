<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * POST /v1/acheteur/paiement/initier
     * Initier un paiement Mobile Money (mock)
     */
    public function initierPaiement(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'numero_mobile'  => 'required|string|min:8|max:15',
        ]);

        $transaction = Transaction::where('id', $data['transaction_id'])
            ->where('acheteur_id', $request->user()->acheteur->id)
            ->where('statut', 'en_attente')
            ->firstOrFail();

        // Mock : simulation d'un appel à l'API Mobile Money
        // En production, ici on appellerait l'API MTN/Moov Money
        $referenceExterne = 'MM-' . strtoupper(Str::random(10));

        $transaction->update([
            'reference_paiement' => $referenceExterne,
            'mode_paiement'      => 'mobile_money',
        ]);

        // Mock : on simule une réponse de l'opérateur
        return response()->json([
            'success' => true,
            'data'    => [
                'reference'       => $referenceExterne,
                'montant'         => $transaction->montant_total,
                'numero_mobile'   => $data['numero_mobile'],
                'statut'          => 'en_attente_confirmation',
                'message_operateur' => 'Veuillez confirmer le paiement sur votre téléphone.',
            ],
            'message' => 'Paiement initié. Confirmez sur votre téléphone.',
        ], 200);
    }

    /**
     * POST /v1/webhooks/paiement
     * Webhook de confirmation paiement (appelé par l'opérateur Mobile Money)
     * Route publique mais sécurisée par signature
     */
    public function webhookConfirmation(Request $request)
    {
        // Vérification basique de la signature webhook
        $signature = $request->header('X-Webhook-Signature');
        $expectedSignature = hash_hmac('sha256', $request->getContent(), config('app.webhook_secret', 'secret'));

        // En mock on accepte tout, en prod on vérifie la signature
        // if ($signature !== $expectedSignature) {
        //     return response()->json(['message' => 'Signature invalide'], 401);
        // }

        $data = $request->validate([
            'reference'  => 'required|string',
            'statut'     => 'required|in:success,failed',
        ]);

        $transaction = Transaction::where('reference_paiement', $data['reference'])
            ->where('statut', 'en_attente')
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction introuvable'], 404);
        }

        if ($data['statut'] === 'success') {
            $result = $this->transactionService->confirmerPaiement($transaction, $data['reference']);

            if (!$result['success']) {
                return response()->json(['message' => $result['message']], 500);
            }
        } else {
            // Paiement échoué → remettre le stock
            $transaction->update(['statut' => 'annulee']);
            $transaction->oeuvre->increment('stock');

            // Si l'œuvre était épuisée, la remettre en validée
            if ($transaction->oeuvre->statut === 'epuisee') {
                $transaction->oeuvre->update(['statut' => 'validee']);
            }
        }

        return response()->json(['message' => 'Webhook traité'], 200);
    }

    /**
     * POST /v1/acheteur/paiement/mock-confirmer
     * Route de test uniquement — simule une confirmation immédiate
     */
    public function mockConfirmer(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        $transaction = Transaction::where('id', $data['transaction_id'])
            ->where('acheteur_id', $request->user()->acheteur->id)
            ->where('statut', 'en_attente')
            ->firstOrFail();

        $reference = $transaction->reference_paiement ?? 'MM-MOCK-' . strtoupper(Str::random(8));
        $result    = $this->transactionService->confirmerPaiement($transaction, $reference);

        return response()->json([
            'success' => $result['success'],
            'data'    => ['transaction' => $result['transaction'] ?? null],
            'message' => $result['success'] ? 'Paiement confirmé (mock)' : $result['message'],
        ], $result['success'] ? 200 : 500);
    }
}
