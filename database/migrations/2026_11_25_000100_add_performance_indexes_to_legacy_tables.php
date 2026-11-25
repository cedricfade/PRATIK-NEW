<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute des index sur les tables historiques pour accélérer l'annuaire,
 * l'immobilier, les news et les pharmacies de garde.
 *
 * Purement additif : aucune donnée, colonne ou table n'est modifiée.
 * Chaque index n'est créé que s'il n'existe pas déjà (ré-exécutable sans risque).
 */
return new class extends Migration
{
    /** @var array<string, array<string, string>> table => [nom index => colonnes SQL] */
    private array $indexes = [
        'entreprises' => [
            'pk_entreprises_lien' => '`LIEN`(191)',
            'pk_entreprises_type' => '`TYPE_ID`',
            'pk_entreprises_commune' => '`COMMUNE_ID`',
            'pk_entreprises_utilisateur' => '`UTILISATEUR`',
        ],
        'immobilier' => [
            'pk_immobilier_entreprise' => '`ENTREPRISE_ID`',
            'pk_immobilier_type' => '`TYPE_IMMOBILIER_ID`',
            'pk_immobilier_categorie' => '`CATEGORIE_IMMOBILIER_ID`',
            'pk_immobilier_utilisateur' => '`UTILISATEUR`',
        ],
        'photo_immobilier' => [
            'pk_photo_immobilier_bien' => '`IMMOBILIER_ID`',
        ],
        'photo_annuaire' => [
            'pk_photo_annuaire_annuaire' => '`ANNUAIRE_ID`',
        ],
        'annuaires' => [
            'pk_annuaires_siege' => '`SIEGE_ID`',
        ],
        'news' => [
            'pk_news_categorie_date' => '`CATEGORIE_ID`, `DATE_PARUTION`',
            'pk_news_entreprise' => '`ENTREPRISE_ID`',
        ],
        'pharmacie_garde' => [
            'pk_pharmacie_garde_entreprise' => '`ENTREPRISE_ID`',
            'pk_pharmacie_garde_periode' => '`DEBUT_GARDE`, `FIN_GARDE`',
        ],
        'sous_categorie' => [
            'pk_sous_categorie_categorie' => '`CATEGORIE_ID`',
            'pk_sous_categorie_lien' => '`LIEN`',
        ],
        'communes' => [
            'pk_communes_lien' => '`LIEN`',
        ],
        'statistiques' => [
            'pk_statistiques_entreprise' => '`ENTREPRISE_ID`, `ANNEE`, `MOIS`',
        ],
        'users' => [
            'pk_users_email' => '`email`(191)',
            'pk_users_entreprise' => '`entreprise_id`',
        ],
        'contact_entreprises' => [
            'pk_contact_entreprises_entreprise' => '`entreprise_id`',
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (! $this->indexExists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$columns})");
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if ($this->indexExists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
                }
            }
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return true; // index MySQL spécifiques : ignorés en SQLite (tests)
        }

        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]))->isNotEmpty();
    }
};
