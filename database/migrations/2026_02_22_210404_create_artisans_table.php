<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id'); // lien vers users
            $table->text('biographie')->nullable();
            $table->string('specialite', 255)->nullable();
            $table->string('region', 255)->nullable();
            $table->string('adresse_atelier', 255)->nullable();
            $table->boolean('compte_valide')->default(false);
            $table->timestamps();

            $table->foreign('utilisateur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};
