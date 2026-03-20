<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajout des index sur la table oeuvres pour optimiser
     * les requêtes du catalogue (filtres, tri, pagination)
     *
     * On vérifie l'existence de chaque index avant de le créer
     * pour éviter l'erreur "Duplicate key name"
     */
    public function up(): void
    {
        Schema::table('oeuvres', function (Blueprint $table) {

            // Récupérer les index déjà existants sur la table
            $existingIndexes = $this->getExistingIndexes('oeuvres');

            // --- INDEX SIMPLES ---
            if (!in_array('oeuvres_artisan_id_index', $existingIndexes)) {
                $table->index('artisan_id');
            }

            if (!in_array('oeuvres_categorie_id_index', $existingIndexes)) {
                $table->index('categorie_id');
            }

            if (!in_array('oeuvres_statut_index', $existingIndexes)) {
                $table->index('statut');
            }

            if (!in_array('oeuvres_prix_index', $existingIndexes)) {
                $table->index('prix');
            }

            if (!in_array('oeuvres_created_at_index', $existingIndexes)) {
                $table->index('created_at');
            }

            if (!in_array('oeuvres_vues_index', $existingIndexes)) {
                $table->index('vues');
            }

            // --- INDEX COMPOSITES ---
            if (!in_array('oeuvres_statut_created_at_index', $existingIndexes)) {
                $table->index(['statut', 'created_at']);
            }

            if (!in_array('oeuvres_statut_prix_index', $existingIndexes)) {
                $table->index(['statut', 'prix']);
            }

            if (!in_array('oeuvres_statut_vues_index', $existingIndexes)) {
                $table->index(['statut', 'vues']);
            }
        });
    }

    /**
     * Suppression des index en cas de rollback
     * On vérifie aussi l'existence avant de supprimer
     */
    public function down(): void
    {
        Schema::table('oeuvres', function (Blueprint $table) {

            $existingIndexes = $this->getExistingIndexes('oeuvres');

            if (in_array('oeuvres_artisan_id_index', $existingIndexes)) {
                $table->dropIndex(['artisan_id']);
            }

            if (in_array('oeuvres_categorie_id_index', $existingIndexes)) {
                $table->dropIndex(['categorie_id']);
            }

            if (in_array('oeuvres_statut_index', $existingIndexes)) {
                $table->dropIndex(['statut']);
            }

            if (in_array('oeuvres_prix_index', $existingIndexes)) {
                $table->dropIndex(['prix']);
            }

            if (in_array('oeuvres_created_at_index', $existingIndexes)) {
                $table->dropIndex(['created_at']);
            }

            if (in_array('oeuvres_vues_index', $existingIndexes)) {
                $table->dropIndex(['vues']);
            }

            if (in_array('oeuvres_statut_created_at_index', $existingIndexes)) {
                $table->dropIndex(['statut', 'created_at']);
            }

            if (in_array('oeuvres_statut_prix_index', $existingIndexes)) {
                $table->dropIndex(['statut', 'prix']);
            }

            if (in_array('oeuvres_statut_vues_index', $existingIndexes)) {
                $table->dropIndex(['statut', 'vues']);
            }
        });
    }

    /**
     * Récupère la liste des noms d'index existants sur une table
     */
    private function getExistingIndexes(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}`");
        return array_map(fn($index) => $index->Key_name, $indexes);
    }
};
