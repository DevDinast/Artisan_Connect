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
        $existingIndexes = $this->getIndexes('oeuvres');

        Schema::table('oeuvres', function (Blueprint $table) use ($existingIndexes) {
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
     */
    public function down(): void
    {
        $existingIndexes = $this->getIndexes('oeuvres');

        Schema::table('oeuvres', function (Blueprint $table) use ($existingIndexes) {
            if (in_array('oeuvres_artisan_id_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_artisan_id_index');
            }
            if (in_array('oeuvres_categorie_id_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_categorie_id_index');
            }
            if (in_array('oeuvres_statut_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_statut_index');
            }
            if (in_array('oeuvres_prix_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_prix_index');
            }
            if (in_array('oeuvres_created_at_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_created_at_index');
            }
            if (in_array('oeuvres_vues_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_vues_index');
            }
            if (in_array('oeuvres_statut_created_at_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_statut_created_at_index');
            }
            if (in_array('oeuvres_statut_prix_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_statut_prix_index');
            }
            if (in_array('oeuvres_statut_vues_index', $existingIndexes)) {
                $table->dropIndex('oeuvres_statut_vues_index');
            }
        });
    }

    private function getIndexes(string $tableName): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $indexes = [];

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$tableName}')");
            foreach ($rows as $row) {
                $indexes[] = $row->name;
            }
        } elseif ($driver === 'mysql') {
            $rows = DB::select("SHOW INDEX FROM `{$tableName}`");
            foreach ($rows as $row) {
                $indexes[] = $row->Key_name;
            }
        } else {
            $rows = DB::select('SELECT indexname AS name FROM pg_indexes WHERE tablename = ?', [$tableName]);
            foreach ($rows as $row) {
                $indexes[] = $row->name;
            }
        }

        return array_unique($indexes);
    }
};
