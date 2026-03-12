<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1. COMPLÉTER LA TABLE OEUVRES ---
Schema::table('oeuvres', function (Blueprint $table) {
    if (!Schema::hasColumn('oeuvres', 'vues'))
        $table->integer('vues')->default(0)->after('statut');
    if (!Schema::hasColumn('oeuvres', 'favoris_count'))
        $table->integer('favoris_count')->default(0)->after('vues');
    if (!Schema::hasColumn('oeuvres', 'motif_refus'))
        $table->string('motif_refus')->nullable()->after('favoris_count');
    if (!Schema::hasColumn('oeuvres', 'date_validation'))
        $table->timestamp('date_validation')->nullable()->after('motif_refus');
    if (!Schema::hasColumn('oeuvres', 'validateur_id'))
        $table->unsignedBigInteger('validateur_id')->nullable()->after('date_validation');
    if (!Schema::hasColumn('oeuvres', 'featured'))
        $table->boolean('featured')->default(false)->after('validateur_id');
    if (!Schema::hasColumn('oeuvres', 'dimensions'))
        $table->json('dimensions')->nullable()->after('featured');
    if (!Schema::hasColumn('oeuvres', 'materiaux'))
        $table->json('materiaux')->nullable()->after('dimensions');
    if (!Schema::hasColumn('oeuvres', 'poids'))
        $table->float('poids')->nullable()->after('materiaux');
    if (!Schema::hasColumn('oeuvres', 'quantite_disponible'))
        $table->integer('quantite_disponible')->default(0)->after('stock');
});

        // --- 2. COMPLÉTER LA TABLE ARTISANS ---
Schema::table('artisans', function (Blueprint $table) {
    if (!Schema::hasColumn('artisans', 'nb_oeuvres_publiees'))
        $table->integer('nb_oeuvres_publiees')->default(0)->after('compte_valide');
    if (!Schema::hasColumn('artisans', 'note_moyenne'))
        $table->float('note_moyenne')->default(0)->after('nb_oeuvres_publiees');
    if (!Schema::hasColumn('artisans', 'nb_ventes'))
        $table->integer('nb_ventes')->default(0)->after('note_moyenne');
});

        // --- 3. COMPLÉTER LA TABLE CATEGORIES ---
Schema::table('categories', function (Blueprint $table) {
    if (!Schema::hasColumn('categories', 'slug'))
        $table->string('slug')->nullable()->after('name');
    if (!Schema::hasColumn('categories', 'parent_id'))
        $table->unsignedBigInteger('parent_id')->nullable()->after('slug');
    if (!Schema::hasColumn('categories', 'icone'))
        $table->string('icone')->nullable()->after('parent_id');
    if (!Schema::hasColumn('categories', 'ordre'))
        $table->integer('ordre')->default(0)->after('icone');
});

        // --- 4. CRÉER LA TABLE TRANSACTIONS ---
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acheteur_id');
            $table->unsignedBigInteger('oeuvre_id');
            $table->decimal('montant_total', 10, 2);
            $table->decimal('commission_plateforme', 10, 2);
            $table->decimal('montant_artisan', 10, 2);
            $table->string('statut')->default('en_attente'); // en_attente, payee, livree, annulee
            $table->string('mode_paiement')->nullable();
            $table->string('reference_paiement')->nullable();
            $table->json('adresse_livraison')->nullable();
            $table->decimal('frais_livraison', 10, 2)->default(0);
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_expedition')->nullable();
            $table->timestamp('date_livraison')->nullable();
            $table->timestamps();

            $table->foreign('acheteur_id')->references('id')->on('acheteurs')->onDelete('cascade');
            $table->foreign('oeuvre_id')->references('id')->on('oeuvres')->onDelete('cascade');
        });

        // --- 5. COMPLÉTER LA TABLE AVIS ---
        Schema::table('avis', function (Blueprint $table) {
            $columns = array_column(DB::select('SHOW COLUMNS FROM avis'), 'Field');
            if (!in_array('transaction_id', $columns))
                $table->unsignedBigInteger('transaction_id')->nullable()->after('id');
            if (!in_array('acheteur_id', $columns))
                $table->unsignedBigInteger('acheteur_id')->nullable()->after('transaction_id');
            if (!in_array('artisan_id', $columns))
                $table->unsignedBigInteger('artisan_id')->nullable()->after('acheteur_id');
            if (!in_array('note', $columns))
                $table->integer('note')->after('artisan_id');
            if (!in_array('commentaire', $columns))
                $table->text('commentaire')->nullable()->after('note');
            if (!in_array('statut', $columns))
                $table->string('statut')->default('publie')->after('commentaire');
        });

        // --- 6. COMPLÉTER LA TABLE FAVORIS ---
        Schema::table('favoris', function (Blueprint $table) {
            $columns = array_column(DB::select('SHOW COLUMNS FROM favoris'), 'Field');
            if (!in_array('acheteur_id', $columns))
                $table->unsignedBigInteger('acheteur_id')->after('id');
            if (!in_array('oeuvre_id', $columns))
                $table->unsignedBigInteger('oeuvre_id')->after('acheteur_id');
        });

        // --- 7. CRÉER LA TABLE NOTIFICATIONS ---
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type');
                $table->string('titre');
                $table->text('message');
                $table->json('data')->nullable();
                $table->boolean('lue')->default(false);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'lue']);
            });
        }

        // --- 8. CRÉER LA TABLE PANIER ---
        if (!Schema::hasTable('panier')) {
            Schema::create('panier', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('acheteur_id');
                $table->unsignedBigInteger('oeuvre_id');
                $table->integer('quantite')->default(1);
                $table->timestamps();

                $table->foreign('acheteur_id')->references('id')->on('acheteurs')->onDelete('cascade');
                $table->foreign('oeuvre_id')->references('id')->on('oeuvres')->onDelete('cascade');
                $table->unique(['acheteur_id', 'oeuvre_id']);
            });
        }

        // --- 9. INDEX SUR OEUVRES ---
        $indexes = array_map(fn($i) => $i->Key_name, DB::select("SHOW INDEX FROM `oeuvres`"));
        Schema::table('oeuvres', function (Blueprint $table) use ($indexes) {
            if (!in_array('oeuvres_statut_index', $indexes)) $table->index('statut');
            if (!in_array('oeuvres_prix_index', $indexes)) $table->index('prix');
            if (!in_array('oeuvres_vues_index', $indexes)) $table->index('vues');
            if (!in_array('oeuvres_statut_created_at_index', $indexes)) $table->index(['statut', 'created_at']);
            if (!in_array('oeuvres_statut_prix_index', $indexes)) $table->index(['statut', 'prix']);
            if (!in_array('oeuvres_statut_vues_index', $indexes)) $table->index(['statut', 'vues']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panier');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('transactions');
    }
};
