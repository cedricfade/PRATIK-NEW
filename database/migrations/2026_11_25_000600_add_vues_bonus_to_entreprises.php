<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vues ajoutées depuis le backoffice (Admin › Vues), séparées du compteur réel COMPTEUR :
 * le site affiche COMPTEUR + VUES_BONUS, et l'on peut revenir aux chiffres réels à tout moment.
 * Purement additif : nouvelle colonne à 0 par défaut + table d'historique.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('entreprises', 'VUES_BONUS')) {
            Schema::table('entreprises', function (Blueprint $table) {
                $table->unsignedInteger('VUES_BONUS')->default(0)->after('COMPTEUR');
            });
        }

        if (! Schema::hasTable('historique_vues')) {
            Schema::create('historique_vues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('perimetre', 50);
                $table->string('libelle');
                $table->string('operation', 20); // ajout | remise_a_zero
                $table->unsignedInteger('minimum')->default(0);
                $table->unsignedInteger('maximum')->default(0);
                $table->unsignedInteger('fiches')->default(0);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_vues');

        if (Schema::hasColumn('entreprises', 'VUES_BONUS')) {
            Schema::table('entreprises', fn (Blueprint $table) => $table->dropColumn('VUES_BONUS'));
        }
    }
};
