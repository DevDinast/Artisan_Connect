<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('oeuvres', function (Blueprint $table) {
        $table->integer('vues')->default(0)->after('statut');
        $table->integer('favoris')->default(0)->after('vues');
        $table->string('motif_refus')->nullable()->after('favoris');
        $table->timestamp('date_validation')->nullable()->after('motif_refus');
        $table->unsignedBigInteger('validateur_id')->nullable()->after('date_validation');
        $table->boolean('featured')->default(false)->after('validateur_id');
        $table->json('dimensions')->nullable()->after('featured');
        $table->json('materiaux')->nullable()->after('dimensions');
        $table->float('poids')->nullable()->after('materiaux');
    });
}

public function down(): void
{
    Schema::table('oeuvres', function (Blueprint $table) {
        $table->dropColumn([
            'vues', 'favoris', 'motif_refus', 'date_validation',
            'validateur_id', 'featured', 'dimensions', 'materiaux', 'poids'
        ]);
    });
}
};
