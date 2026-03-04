<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('oeuvre_id');
            $table->string('chemin');
            $table->boolean('is_principale')->default(false);
            $table->timestamps();

            $table->foreign('oeuvre_id')->references('id')->on('oeuvres')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
