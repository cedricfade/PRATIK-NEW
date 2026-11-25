<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Réglages modifiables depuis le backoffice (heros des pages d'accueil, chiffres…). Nouvelle table. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('parametres_site')) {
            Schema::create('parametres_site', function (Blueprint $table) {
                $table->string('cle', 100)->primary();
                $table->text('valeur')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_site');
    }
};
