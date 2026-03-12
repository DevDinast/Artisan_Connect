<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Panier;
use App\Models\Oeuvre;
use App\Models\Notification;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function getCommandes(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $commandes = Transaction::where('acheteur_id', $acheteur->id)
            ->with(['oeuvre.images', 'oeuvre.artisan', 'avis'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => [
                'commandes'  => $commandes->items(),
                'pagination' => [
                    'total'        => $commandes->total(),
                    'current_page' => $commandes->currentPage(),
                    'last_page'    => $commandes->lastPage(),
                ],
            ],
            'message' => 'Commandes récupérées avec succès',
        ], 200);
    }

    public function creerCommande(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $data = $request->validate([
            'oeuvre_id'         => 'required|exists:oeuvres,id',
            'quantite'          => 'integer|min:1',
            'adresse_livraison' => 'required|array',
            'mode_paiement'     => 'required|string|in:mobile_money,carte',
        ]);

        $oeuvre = Oeuvre::where('statut', 'validee')->findOrFail($data['oeuvre_id']);

        $result = $this->transactionService->creerCommande($acheteur, $oeuvre, $data);

        return response()->json([
            'success' => $result['success'],
            'data'    => isset($result['transaction']) ? ['transaction' => $result['transaction']] : null,
            'message' => $result['success'] ? 'Commande créée avec succès' : $result['message'],
        ], $result['success'] ? 201 : 422);
    }
}
