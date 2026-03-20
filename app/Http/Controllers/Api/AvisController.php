<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Transaction;
use App\Models\Artisan;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function getAvisOeuvre($id)
{
    $avis = Avis::whereHas('transaction', fn($q) => $q->where('oeuvre_id', $id))
        ->where('statut', 'publie')
        ->with('acheteur.utilisateur')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return response()->json([
        'success' => true,
        'data'    => ['avis' => $avis->items()],
        'message' => 'Avis récupérés avec succès',
    ], 200);
}

    public function creerAvis(Request $request)
    {
        $acheteur = $request->user()->acheteur;

        $data = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'note'           => 'required|integer|min:1|max:5',
            'commentaire'    => 'nullable|string|max:1000',
        ]);

        $transaction = Transaction::where('id', $data['transaction_id'])
            ->where('acheteur_id', $acheteur->id)
            ->where('statut', 'payee')
            ->firstOrFail();

        if (Avis::where('transaction_id', $transaction->id)->exists()) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Vous avez déjà laissé un avis pour cette commande.',
            ], 422);
        }

        $avis = Avis::create([
            'transaction_id' => $transaction->id,
            'acheteur_id'    => $acheteur->id,
            'oeuvre_id'      => $transaction->oeuvre_id,
            'artisan_id'     => $transaction->oeuvre->artisan_id,
            'note'           => $data['note'],
            'commentaire'    => $data['commentaire'],
            'statut'         => 'publie',
        ]);

        $artisan     = $transaction->oeuvre->artisan;
        $noteMoyenne = Avis::where('artisan_id', $artisan->id)->avg('note');
        $artisan->update(['note_moyenne' => round($noteMoyenne, 2)]);

        return response()->json([
            'success' => true,
            'data'    => ['avis' => $avis],
            'message' => 'Avis publié avec succès',
        ], 201);
    }

    public function getStatistiquesAvisArtisan($id)
    {
        $artisan = Artisan::findOrFail($id);

        $stats = [
            'note_moyenne' => $artisan->note_moyenne,
            'nb_avis'      => Avis::where('artisan_id', $id)->count(),
            'repartition'  => Avis::where('artisan_id', $id)
                ->selectRaw('note, COUNT(*) as total')
                ->groupBy('note')
                ->pluck('total', 'note'),
        ];

        return response()->json([
            'success' => true,
            'data'    => ['stats' => $stats],
            'message' => 'Statistiques récupérées avec succès',
        ], 200);
    }
}
