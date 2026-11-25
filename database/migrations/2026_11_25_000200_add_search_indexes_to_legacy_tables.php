<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index de recherche :
 *  - FULLTEXT sur entreprises (TITRE, SIGLE, ACTIVITE) : la recherche globale
 *    n'a plus à parcourir les ~100 000 lignes avec des LIKE '%…%'.
 *  - index composites pour les listes immobilier / annuaire les plus consultées.
 *
 * Additif et ré-exécutable. Compatible MySQL 8 et MariaDB 10.x / 11.x.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return;
        }

        $this->add('entreprises', 'ft_entreprises_recherche', 'FULLTEXT INDEX `ft_entreprises_recherche` (`TITRE`, `SIGLE`, `ACTIVITE`)');
        $this->add('immobilier', 'pk_immobilier_validation_type', 'INDEX `pk_immobilier_validation_type` (`VALIDATION`, `TYPE_IMMOBILIER_ID`, `ID`)');
        $this->add('thematique_annuaire', 'pk_thematique_sous_cat_entreprise', 'INDEX `pk_thematique_sous_cat_entreprise` (`SOUS_CATEGORIE_ID`, `ENTREPRISE_ID`)');
        $this->add('thematique_annuaire', 'pk_thematique_cat_entreprise', 'INDEX `pk_thematique_cat_entreprise` (`CATEGORIE_ID`, `ENTREPRISE_ID`)');
        $this->add('annuaires', 'pk_annuaires_client', 'INDEX `pk_annuaires_client` (`CLIENT`, `ENTREPRISE_ID`)');
        $this->add('news', 'pk_news_categorie_id', 'INDEX `pk_news_categorie_id` (`CATEGORIE_ID`, `ID`)');
    }

    public function down(): void
    {
        foreach ([
            'entreprises' => 'ft_entreprises_recherche',
            'immobilier' => 'pk_immobilier_validation_type',
            'thematique_annuaire' => ['pk_thematique_sous_cat_entreprise', 'pk_thematique_cat_entreprise'],
            'annuaires' => 'pk_annuaires_client',
            'news' => 'pk_news_categorie_id',
        ] as $table => $names) {
            foreach ((array) $names as $name) {
                if ($this->exists($table, $name)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
                }
            }
        }
    }

    private function add(string $table, string $name, string $definition): void
    {
        if (Schema::hasTable($table) && ! $this->exists($table, $name)) {
            DB::statement("ALTER TABLE `{$table}` ADD {$definition}");
        }
    }

    private function exists(string $table, string $name): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]))->isNotEmpty();
    }
};
