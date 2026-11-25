<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bannières publicitaires de l'application mobile (carrousel de l'accueil). Nouvelle table. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pubs_app')) {
            Schema::create('pubs_app', function (Blueprint $table) {
                $table->id();
                $table->string('titre', 150)->nullable();
                $table->string('image');
                $table->string('lien', 500)->nullable();
                $table->unsignedSmallInteger('ordre')->default(0);
                $table->boolean('actif')->default(true);
                $table->date('debut')->nullable();
                $table->date('fin')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pubs_app');
    }
};
