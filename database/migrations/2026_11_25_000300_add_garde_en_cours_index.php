<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Gardes en cours" = DEBUT_GARDE <= aujourd'hui <= FIN_GARDE.
 * Un index qui commence par FIN_GARDE ne lit que les gardes en cours ou à venir,
 * au lieu de tout l'historique (qui grossit chaque semaine). Additif.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true) || ! Schema::hasTable('pharmacie_garde')) {
            return;
        }

        if (collect(DB::select("SHOW INDEX FROM `pharmacie_garde` WHERE Key_name = 'pk_pharmacie_garde_fin_debut'"))->isEmpty()) {
            DB::statement('ALTER TABLE `pharmacie_garde` ADD INDEX `pk_pharmacie_garde_fin_debut` (`FIN_GARDE`, `DEBUT_GARDE`, `ENTREPRISE_ID`)');
        }
    }

    public function down(): void
    {
        if (collect(DB::select("SHOW INDEX FROM `pharmacie_garde` WHERE Key_name = 'pk_pharmacie_garde_fin_debut'"))->isNotEmpty()) {
            DB::statement('ALTER TABLE `pharmacie_garde` DROP INDEX `pk_pharmacie_garde_fin_debut`');
        }
    }
};
