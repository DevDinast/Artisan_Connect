<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oeuvre;
use App\Models\Transaction;
use App\Services\OeuvreService;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ArtisanController extends Controller
{
    public function __construct(
        private OeuvreService $oeuvreService,
        private ImageService  $imageService
    ) {}

    /**
     * GET /v1/artisan/dashboard
     */
    public function dashboard(Request $request)
    {
        $artisan = $request->user()->artisan;

        return response()->json([
            'success' => true,
            'data'    => [
                'stats' => [
                    'nb_oeuvres_publiees'  => Oeuvre::where('artisan_id', $artisan->id)->where('statut', 'validee')->count(),
                    'nb_oeuvres_attente'   => Oeuvre::where('artisan_id', $artisan->id)->where('statut', 'en_attente')->count(),
                    'nb_oeuvres_brouillon' => Oeuvre::where('artisan_id', $artisan->id)->where('statut', 'brouillon')->count(),
                    'nb_oeuvres_refusees'  => Oeuvre::where('artisan_id', $artisan->id)->where('statut', 'refusee')->count(),
                    'total_vues'           => Oeuvre::where('artisan_id', $artisan->id)->sum('vues'),
                    'note_moyenne'         => $artisan->note_moyenne,
                    'nb_ventes'            => $artisan->nb_ventes,
                    'revenus_total'        => Transaction::whereHas('oeuvre', fn($q) => $q->where('artisan_id', $artisan->id))
                                                ->where('statut', 'payee')->sum('montant_artisan'),
                ],
            ],
            'message' => 'Dashboard récupéré avec succès',
        ], 200);
    }

    /**
     * GET /v1/artisan/oeuvres
     */
    public function mesOeuvres(Request $request)
    {
        $artisan = $request->user()->artisan;

        $query = Oeuvre::where('artisan_id', $artisan->id)
            ->with(['categorie', 'images'])
            ->orderBy('created_at', 'desc');

        $query->when($request->statut, fn($q, $v) => $q->where('statut', $v));

        $oeuvres = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => [
                'oeuvres'    => $oeuvres->items(),
                'pagination' => [
                    'total'        => $oeuvres->total(),
                    'current_page' => $oeuvres->currentPage(),
                    'last_page'    => $oeuvres->lastPage(),
                ],
            ],
            'message' => 'Œuvres récupérées avec succès',
        ], 200);
    }

    /**
     * POST /v1/artisan/oeuvres
     * RG06 : max 50 œuvres en attente
     * RG09 : champs obligatoires
     */
    public function creerOeuvre(Request $request)
    {
        $artisan = $request->user()->artisan;

        // RG06
        $nbEnAttente = Oeuvre::where('artisan_id', $artisan->id)->where('statut', 'en_attente')->count();
        if ($nbEnAttente >= 50) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Limite de 50 œuvres en attente atteinte.',
            ], 422);
        }

        // RG09 : champs obligatoires
        $data = $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
            'prix'         => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'dimensions'   => 'nullable|array',
            'materiaux'    => 'nullable|array',
            'poids'        => 'nullable|numeric',
        ]);

        $data['artisan_id'] = $artisan->id;
        $data['statut']     = 'brouillon';

        $oeuvre = Oeuvre::create($data);

        return response()->json([
            'success' => true,
            'data'    => ['oeuvre' => $oeuvre],
            'message' => 'Œuvre créée avec succès',
        ], 201);
    }

    /**
     * GET /v1/artisan/oeuvres/{id}
     */
    public function detailOeuvre(Request $request, $id)
    {
        $artisan = $request->user()->artisan;
        $oeuvre  = Oeuvre::where('artisan_id', $artisan->id)->with(['categorie', 'images'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => ['oeuvre' => $oeuvre],
            'message' => 'Œuvre récupérée avec succès',
        ], 200);
    }

    /**
     * PUT /v1/artisan/oeuvres/{id}
     */
    public function mettreAJourOeuvre(Request $request, $id)
{
    $artisan = $request->user()->artisan;
    $oeuvre  = Oeuvre::where('artisan_id', $artisan->id)->findOrFail($id);

    // Bloquer uniquement si l'œuvre est validée
    if ($oeuvre->statut === 'validee') {
        return response()->json([
            'success' => false,
            'message' => 'Une œuvre validée ne peut pas être modifiée.',
        ], 403);
    }

    $data = $request->validate([
        'titre'        => 'sometimes|string|max:255',
        'description'  => 'sometimes|string',
        'prix'         => 'sometimes|numeric|min:0',
        'stock'        => 'sometimes|integer|min:0',
        'categorie_id' => 'sometimes|exists:categories,id',
    ]);

    // Repasser en brouillon si l'œuvre était en attente ou refusée
    if (in_array($oeuvre->statut, ['en_attente', 'refusee'])) {
        $data['statut']      = 'brouillon';
        $data['motif_refus'] = null;
    }

    $oeuvre->update($data);

    return response()->json([
        'success' => true,
        'data'    => ['oeuvre' => $oeuvre->fresh()->load(['categorie', 'images'])],
        'message' => 'Œuvre mise à jour avec succès',
    ], 200);
}

    /**
     * DELETE /v1/artisan/oeuvres/{id}
     */
    public function supprimerOeuvre(Request $request, $id)
    {
        $artisan = $request->user()->artisan;
        $oeuvre  = Oeuvre::where('artisan_id', $artisan->id)->findOrFail($id);

        $this->oeuvreService->supprimer($oeuvre);

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Œuvre supprimée avec succès',
        ], 200);
    }

    /**
     * PUT /v1/artisan/oeuvres/{id}/soumettre
     * Soumettre une œuvre : brouillon → en_attente
     */
    public function soumettre(Request $request, $id)
    {
        $artisan = $request->user()->artisan;
        $oeuvre  = Oeuvre::where('artisan_id', $artisan->id)->findOrFail($id);

        $result = $this->oeuvreService->soumettre($oeuvre, $artisan);

        return response()->json([
            'success' => $result['success'],
            'data'    => isset($result['oeuvre']) ? ['oeuvre' => $result['oeuvre']] : null,
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }

    /**
     * GET /v1/artisan/ventes
     */
    public function mesVentes(Request $request)
    {
        $artisan = $request->user()->artisan;

        $ventes = Transaction::whereHas('oeuvre', fn($q) => $q->where('artisan_id', $artisan->id))
            ->where('statut', 'payee')
            ->with(['oeuvre', 'acheteur.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => [
                'ventes'     => $ventes->items(),
                'pagination' => [
                    'total'        => $ventes->total(),
                    'current_page' => $ventes->currentPage(),
                    'last_page'    => $ventes->lastPage(),
                ],
            ],
            'message' => 'Ventes récupérées avec succès',
        ], 200);
    }
}
