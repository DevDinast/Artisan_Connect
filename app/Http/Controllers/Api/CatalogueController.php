<?php

namespace App\Http\Controllers\Api;

use App\Models\Oeuvre;

class CatalogueController extends Controller {
    public function index() {
        $oeuvres = Oeuvre::all(); // ou filtrer selon besoin
        return response()->json($oeuvres);
    }
}