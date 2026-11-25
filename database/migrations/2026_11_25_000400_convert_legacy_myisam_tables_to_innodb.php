<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Quelques tables historiques (users, contacts, roles…) sont en MyISAM.
 * InnoDB résiste aux coupures (pas de table "crashed" à réparer), verrouille
 * par ligne au lieu de bloquer toute la table, et gère les transactions.
 *
 * Les données ne sont pas modifiées : seul le moteur de stockage change.
 * Tables concernées : toutes petites (moins de 3 000 lignes), conversion instantanée.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $tables = DB::select(
            "SELECT TABLE_NAME AS nom FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'MyISAM' AND TABLE_TYPE = 'BASE TABLE'"
        );

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table->nom}` ENGINE=InnoDB");
        }
    }

    public function down(): void
    {
        // Pas de retour vers MyISAM : InnoDB est compatible avec l'ancien code.
    }
};
