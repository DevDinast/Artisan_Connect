<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oeuvre;
use App\Models\Artisan;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * GET /v1/admin/dashboard
     */
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'oeuvres_en_attente' => Oeuvre::where('statut', 'en_attente')->count(),
                    'oeuvres_validees'   => Oeuvre::where('statut', 'validee')->count(),
                    'oeuvres_refusees'   => Oeuvre::where('statut', 'refusee')->count(),
                    'total_artisans'     => Artisan::count(),
                    'artisans_valides'   => Artisan::where('compte_valide', true)->count(),
                    'total_transactions' => Transaction::count(),
                    'ca_total'           => Transaction::where('statut', 'payee')->sum('montant_total'),
                    'commissions_total'  => Transaction::where('statut', 'payee')->sum('commission_plateforme'),
                ],
            ],
            'message' => 'Dashboard admin récupéré avec succès',
        ], 200);
    }

    /**
     * GET /v1/admin/oeuvres/en-attente
     */
    public function getOeuvresEnAttente(Request $request)
    {
        $oeuvres = Oeuvre::where('statut', 'en_attente')
            ->with(['artisan', 'categorie', 'images'])
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => [
                'oeuvres'    => $oeuvres->map(fn($o) => [
                    'id'          => $o->id,
                    'titre'       => $o->titre,
                    'description' => $o->description,
                    'prix'        => $o->prix,
                    'statut'      => $o->statut,
                    'created_at'  => $o->created_at,
                    'images'      => $o->images->map(fn($i) => ['url' => $i->url]),
                    'categorie'   => ['id' => $o->categorie?->id, 'name' => $o->categorie?->name],
                    // ✅ Récupérer le nom artisan directement depuis User via artisan_id
                    'artisan'     => [
                        'id'   => $o->artisan?->id,
                        'name' => \App\Models\User::find($o->artisan?->user_id)?->name ?? '—',
                    ],
                ]),
                'pagination' => [
                    'total'        => $oeuvres->total(),
                    'current_page' => $oeuvres->currentPage(),
                    'last_page'    => $oeuvres->lastPage(),
                ],
            ],
            'message' => 'File de validation récupérée avec succès',
        ], 200);
    }

    /**
     * PUT /v1/admin/oeuvres/{id}/valider
     */
    public function validerOeuvre(Request $request, $id)
    {
        $admin  = $request->user();
        $oeuvre = Oeuvre::where('statut', 'en_attente')->findOrFail($id);

        // RG13 : double validation pour œuvres > 500 000 FCFA
        if ($oeuvre->prix > 500000 && !$request->boolean('double_validation_confirmee')) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Cette œuvre dépasse 500 000 FCFA et nécessite une double validation. Ajoutez double_validation_confirmee=true.',
            ], 422);
        }

        $oeuvre->update([
            'statut'          => 'validee',
            'date_validation' => now(),
            'validateur_id'   => $admin->id,
            'motif_refus'     => null,
        ]);

        $oeuvre->artisan->increment('nb_oeuvres_publiees');

        // ✅ Récupérer le user_id correctement
        $userId = \App\Models\Artisan::find($oeuvre->artisan_id)?->user_id;

        if ($userId) {
            $this->notificationService->notifier(
                $userId,
                'validation',
                'Œuvre validée !',
                "Votre œuvre \"{$oeuvre->titre}\" a été validée et est visible dans le catalogue."
            );
        }

        return response()->json([
            'success' => true,
            'data'    => ['oeuvre' => $oeuvre->fresh()->load(['artisan', 'categorie', 'images'])],
            'message' => 'Œuvre validée et publiée avec succès',
        ], 200);
    }

    /**
     * PUT /v1/admin/oeuvres/{id}/refuser
     */
    public function refuserOeuvre(Request $request, $id)
    {
        $admin  = $request->user();
        $oeuvre = Oeuvre::where('statut', 'en_attente')->findOrFail($id);

        $data = $request->validate([
            'motif_refus' => 'required|string|min:10',
        ]);

        $oeuvre->update([
            'statut'          => 'refusee',
            'motif_refus'     => $data['motif_refus'],
            'date_validation' => now(),
            'validateur_id'   => $admin->id,
        ]);

        // ✅ Récupérer le user_id correctement
        $userId = \App\Models\Artisan::find($oeuvre->artisan_id)?->user_id;

        if ($userId) {
            $this->notificationService->notifier(
                $userId,
                'refus',
                'Œuvre refusée',
                "Votre œuvre \"{$oeuvre->titre}\" a été refusée. Motif : {$data['motif_refus']}"
            );
        }

        return response()->json([
            'success' => true,
            'data'    => ['oeuvre' => $oeuvre->fresh()],
            'message' => 'Œuvre refusée avec succès',
        ], 200);
    }
}
