<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panier_items', function (Blueprint $table) {
            $table->foreignId('acheteur_id')->constrained('acheteurs')->onDelete('cascade');
            $table->foreignId('oeuvre_id')->constrained('oeuvres')->onDelete('cascade');
            $table->integer('quantite')->default(1);
        });
    }
    public function down(): void
    {
        Schema::table('panier_items', function (Blueprint $table) {
            $table->dropForeign(['acheteur_id']);
            $table->dropForeign(['oeuvre_id']);
            $table->dropColumn(['acheteur_id', 'oeuvre_id', 'quantite']);
        });
    }
};
