<?php

namespace App\Http\Controllers\Api;

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
        $categories = Categorie::withCount([
            'oeuvres as total_oeuvres' => fn($q) => $q->where('statut', 'publie'),
        ])
        ->having('total_oeuvres', '>', 0)
        ->orderByDesc('total_oeuvres')
        ->get(['id', 'nom', 'slug', 'description']);

        return response()->json(['data' => $categories]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/oeuvres
    |--------------------------------------------------------------------------
    | Params : search, categorie, tri (recent|prix_asc|prix_desc), per_page
    */
    public function oeuvres(Request $request): JsonResponse
    {
        $query = Oeuvre::query()
            ->where('statut', 'publie')
            ->with(['images', 'categorie', 'artisan:id,name,specialite,avatar_url']);

        // Filtre recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre catégorie
        if ($categorie = $request->input('categorie')) {
            $query->whereHas('categorie', fn($q) =>
                $q->where('id', $categorie)
                  ->orWhere('slug', $categorie)
                  ->orWhere('nom', $categorie)
            );
        }

        // Tri
        match ($request->input('tri')) {
            'recent'    => $query->latest(),
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
                'image'     => $o->images->first()?->url ?? null,
                'images'    => $o->images->map(fn($i) => ['url' => $i->url]),
                'categorie' => [
                    'id'  => $o->categorie?->id,
                    'nom' => $o->categorie?->nom,
                ],
                'artisan'   => [
                    'id'         => $o->artisan?->id,
                    'name'       => $o->artisan?->name,
                    'specialite' => $o->artisan?->specialite,
                    'avatar'     => $o->artisan?->avatar_url,
                ],
            ]),
            'meta' => [
                'total'        => $oeuvres->total(),
                'current_page' => $oeuvres->currentPage(),
                'last_page'    => $oeuvres->lastPage(),
                'per_page'     => $oeuvres->perPage(),
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
        $oeuvre = Oeuvre::where('statut', 'publie')
            ->with(['images', 'categorie', 'artisan:id,name,bio,specialite,avatar_url'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id'          => $oeuvre->id,
                'titre'       => $oeuvre->titre,
                'description' => $oeuvre->description,
                'prix'        => $oeuvre->prix,
                'statut'      => $oeuvre->statut,
                'images'      => $oeuvre->images->map(fn($i) => ['url' => $i->url]),
                'categorie'   => [
                    'id'  => $oeuvre->categorie?->id,
                    'nom' => $oeuvre->categorie?->nom,
                ],
                'artisan'     => [
                    'id'         => $oeuvre->artisan?->id,
                    'name'       => $oeuvre->artisan?->name,
                    'bio'        => $oeuvre->artisan?->bio,
                    'specialite' => $oeuvre->artisan?->specialite,
                    'avatar'     => $oeuvre->artisan?->avatar_url,
                ],
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/oeuvres/{id}/similar
    |--------------------------------------------------------------------------
    */
    public function similarOeuvres(int $id): JsonResponse
    {
        $oeuvre = Oeuvre::where('statut', 'publie')->findOrFail($id);

        $similaires = Oeuvre::where('statut', 'publie')
            ->where('id', '!=', $id)
            ->where('categorie_id', $oeuvre->categorie_id)
            ->with(['images', 'categorie'])
            ->latest()
            ->limit(4)
            ->get();

        return response()->json([
            'data' => $similaires->map(fn($o) => [
                'id'        => $o->id,
                'titre'     => $o->titre,
                'prix'      => $o->prix,
                'image'     => $o->images->first()?->url ?? null,
                'categorie' => $o->categorie?->nom,
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
        return response()->json([
            'data' => [
                'total_oeuvres'   => Oeuvre::where('statut', 'publie')->count(),
                'total_artisans'  => User::where('role', 'artisan')->where('is_active', true)->count(),
                'total_categories'=> Categorie::withCount([
                    'oeuvres as total' => fn($q) => $q->where('statut', 'publie'),
                ])->having('total', '>', 0)->count(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/catalog/artisans
    |--------------------------------------------------------------------------
    | Params : search, categorie, per_page
    */
    public function artisans(Request $request): JsonResponse
    {
        $query = User::query()
            ->where('role', 'artisan')
            ->where('is_active', true)
            ->withCount([
                'oeuvres as total_oeuvres' => fn($q) => $q->where('statut', 'publie'),
                'avisRecus as total_avis',
            ])
            ->withAvg('avisRecus as note_moyenne', 'note');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categorie = $request->input('categorie')) {
            $query->whereHas('oeuvres', fn($q) =>
                $q->where('statut', 'publie')
                  ->whereHas('categorie', fn($q2) =>
                      $q2->where('id', $categorie)
                         ->orWhere('slug', $categorie)
                         ->orWhere('nom', $categorie)
                  )
            );
        }

        $artisans = $query
            ->orderByDesc('total_oeuvres')
            ->paginate($request->integer('per_page', 12));

        return response()->json([
            'data' => $artisans->map(fn($a) => [
                'id'            => $a->id,
                'name'          => $a->name,
                'bio'           => $a->bio          ?? null,
                'specialite'    => $a->specialite   ?? null,
                'avatar'        => $a->avatar_url   ?? null,
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
            ->where('is_active', true)
            ->withCount([
                'oeuvres as total_oeuvres' => fn($q) => $q->where('statut', 'publie'),
                'avisRecus as total_avis',
            ])
            ->withAvg('avisRecus as note_moyenne', 'note')
            ->findOrFail($id);

        $oeuvres = $artisan->oeuvres()
            ->where('statut', 'publie')
            ->with(['images', 'categorie'])
            ->latest()
            ->get();

        return response()->json([
            'data' => [
                'id'            => $artisan->id,
                'name'          => $artisan->name,
                'bio'           => $artisan->bio        ?? null,
                'specialite'    => $artisan->specialite ?? null,
                'avatar'        => $artisan->avatar_url ?? null,
                'total_oeuvres' => $artisan->total_oeuvres,
                'total_avis'    => $artisan->total_avis,
                'note_moyenne'  => round($artisan->note_moyenne ?? 0, 1),
                'oeuvres'       => $oeuvres->map(fn($o) => [
                    'id'        => $o->id,
                    'titre'     => $o->titre,
                    'prix'      => $o->prix,
                    'image'     => $o->images->first()?->url ?? null,
                    'categorie' => $o->categorie?->nom,
                ]),
            ],
        ]);
    }
}
