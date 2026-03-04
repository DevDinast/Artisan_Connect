<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oeuvres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artisan_id');
            $table->unsignedBigInteger('categorie_id');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->decimal('prix', 12, 2);
            $table->integer('stock')->default(1);
            $table->string('statut', 50)->default('valide');
            $table->timestamps();

            $table->foreign('artisan_id')->references('id')->on('artisans')->onDelete('cascade');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oeuvres');
    }
};
