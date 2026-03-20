<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Oeuvre;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/categories
    |--------------------------------------------------------------------------
    */
    public function categories(): JsonResponse
    {
        $categories = Categorie::orderBy('name')->get(['id', 'name', 'description']);
        return response()->json(['data' => $categories]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/oeuvres
    |--------------------------------------------------------------------------
    */
    public function oeuvres(Request $request): JsonResponse
    {
        $query = Oeuvre::query()
            ->where('statut', 'validee')
            // ✅ CORRECTION : charger artisan.user pour accéder au nom
            ->with(['images', 'categorie', 'artisan.user']);

        if ($search = $request->input('search')) {
            $query->where(fn($q) =>
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            );
        }

        if ($categorie = $request->input('categorie')) {
            $query->whereHas('categorie', fn($q) =>
                $q->where('id', $categorie)->orWhere('name', $categorie)
            );
        }

        match ($request->input('tri')) {
            'prix_asc'  => $query->orderBy('prix', 'asc'),
            'prix_desc' => $query->orderBy('prix', 'desc'),
            default     => $query->latest(),
        };

        $oeuvres = $query->paginate($request->integer('per_page', 12));

        return response()->json([
            'data' => $oeuvres->map(fn($o) => [
                'id'        => $o->id,
                'titre'     => $o->titre,
                'prix'      => $o->prix,
                'statut'    => $o->statut,
                'image'     => $o->images->first()?->url ?? ($o->images->first()?->chemin ? '/storage/' . $o->images->first()->chemin : null),
                'images'    => $o->images->map(fn($i) => [
                    'url' => $i->url ?? ($i->chemin ? '/storage/' . $i->chemin : null),
                ]),
                'categorie' => ['id' => $o->categorie?->id, 'nom' => $o->categorie?->name],
                // ✅ CORRECTION : nom depuis artisan.user
                'artisan'   => [
                    'id'         => $o->artisan?->id,
                    'name'       => $o->artisan?->user?->name,
                    'specialite' => $o->artisan?->specialite,
                    'avatar'     => $o->artisan?->user?->avatar,
                ],
            ]),
            'meta' => [
                'total'        => $oeuvres->total(),
                'current_page' => $oeuvres->currentPage(),
                'last_page'    => $oeuvres->lastPage(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/oeuvres/{id}
    |--------------------------------------------------------------------------
    */
    public function showOeuvre(int $id): JsonResponse
    {
        $oeuvre = Oeuvre::where('statut', 'validee')
            // ✅ CORRECTION : charger artisan.user
            ->with(['images', 'categorie', 'artisan.user'])
            ->findOrFail($id);

        return response()->json(['data' => [
            'id'          => $oeuvre->id,
            'titre'       => $oeuvre->titre,
            'description' => $oeuvre->description,
            'prix'        => $oeuvre->prix,
            'statut'      => $oeuvre->statut,
            'images'      => $oeuvre->images->map(fn($i) => [
                'url' => $i->url ?? ($i->chemin ? '/storage/' . $i->chemin : null),
            ]),
            'categorie'   => ['id' => $oeuvre->categorie?->id, 'nom' => $oeuvre->categorie?->name],
            'artisan'     => [
                'id'         => $oeuvre->artisan?->id,
                'name'       => $oeuvre->artisan?->user?->name,
                'bio'        => $oeuvre->artisan?->biographie,
                'specialite' => $oeuvre->artisan?->specialite,
                'avatar'     => $oeuvre->artisan?->user?->avatar,
            ],
        ]]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/oeuvres/{id}/similar
    |--------------------------------------------------------------------------
    */
    public function similarOeuvres(int $id): JsonResponse
    {
        $oeuvre = Oeuvre::where('statut', 'validee')->findOrFail($id);

        $similaires = Oeuvre::where('statut', 'validee')
            ->where('id', '!=', $id)
            ->where('categorie_id', $oeuvre->categorie_id)
            ->with(['images', 'categorie'])
            ->latest()->limit(4)->get();

        return response()->json([
            'data' => $similaires->map(fn($o) => [
                'id'        => $o->id,
                'titre'     => $o->titre,
                'prix'      => $o->prix,
                'image'     => $o->images->first()?->url ?? ($o->images->first()?->chemin ? '/storage/' . $o->images->first()->chemin : null),
                'categorie' => $o->categorie?->name,
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/stats
    |--------------------------------------------------------------------------
    */
    public function stats(): JsonResponse
    {
        return response()->json(['data' => [
            'total_oeuvres'    => Oeuvre::where('statut', 'validee')->count(),
            'total_artisans'   => User::where('role', 'artisan')->count(),
            'total_categories' => Categorie::count(),
        ]]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/artisans
    |--------------------------------------------------------------------------
    */
    public function artisans(Request $request): JsonResponse
    {
        $query = User::query()
            ->where('role', 'artisan')
            ->withCount([
                'oeuvres as total_oeuvres' => fn($q) => $q->where('statut', 'validee'),
                'avisRecus as total_avis',
            ])
            ->withAvg('avisRecus as note_moyenne', 'note')
            ->with('artisan');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categorie = $request->input('categorie')) {
            $query->whereHas('oeuvres', fn($q) =>
                $q->where('statut', 'validee')
                  ->whereHas('categorie', fn($q2) =>
                      $q2->where('id', $categorie)->orWhere('name', $categorie)
                  )
            );
        }

        $artisans = $query->orderByDesc('total_oeuvres')
            ->paginate($request->integer('per_page', 12));

        return response()->json([
            'data' => $artisans->map(fn($a) => [
                'id'            => $a->id,
                'name'          => $a->name,
                'bio'           => $a->artisan?->biographie ?? null,
                'specialite'    => $a->artisan?->specialite ?? null,
                'avatar'        => $a->avatar ? '/storage/' . $a->avatar : null,
                'total_oeuvres' => $a->total_oeuvres,
                'total_avis'    => $a->total_avis,
                'note_moyenne'  => round($a->note_moyenne ?? 0, 1),
            ]),
            'meta' => [
                'total'        => $artisans->total(),
                'current_page' => $artisans->currentPage(),
                'last_page'    => $artisans->lastPage(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/artisans/{id}
    |--------------------------------------------------------------------------
    */
    public function showArtisan(int $id): JsonResponse
    {
        $artisan = User::where('role', 'artisan')
            ->withCount([
                'oeuvres as total_oeuvres' => fn($q) => $q->where('statut', 'validee'),
                'avisRecus as total_avis',
            ])
            ->withAvg('avisRecus as note_moyenne', 'note')
            ->with('artisan')
            ->findOrFail($id);

        $oeuvres = $artisan->oeuvres()
            ->where('statut', 'validee')
            ->with(['images', 'categorie'])
            ->latest()->get();

        return response()->json(['data' => [
            'id'            => $artisan->id,
            'name'          => $artisan->name,
            'bio'           => $artisan->artisan?->biographie ?? null,
            'specialite'    => $artisan->artisan?->specialite ?? null,
            'avatar'        => $artisan->avatar ? '/storage/' . $artisan->avatar : null,
            'total_oeuvres' => $artisan->total_oeuvres,
            'total_avis'    => $artisan->total_avis,
            'note_moyenne'  => round($artisan->note_moyenne ?? 0, 1),
            'oeuvres'       => $oeuvres->map(fn($o) => [
                'id'        => $o->id,
                'titre'     => $o->titre,
                'prix'      => $o->prix,
                'image'     => $o->images->first()?->url ?? ($o->images->first()?->chemin ? '/storage/' . $o->images->first()->chemin : null),
                'categorie' => $o->categorie?->name,
            ]),
        ]]);
    }
}
