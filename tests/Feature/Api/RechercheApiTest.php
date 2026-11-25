<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;

/**
 * L'index FULLTEXT d'InnoDB ne voit que les lignes validées (COMMIT) : ce test
 * écrit donc réellement en base puis vide les tables utilisées.
 */
class RechercheApiTest extends ApiTestCase
{
    use DatabaseTruncation;

    protected array $tablesToTruncate = ['entreprises', 'annuaires', 'communes', 'oauth_clients'];

    protected function tearDown(): void
    {
        foreach ($this->tablesToTruncate as $table) {
            DB::table($table)->truncate();
        }

        parent::tearDown();
    }

    public function test_recherche_plein_texte_avec_filtre_commune(): void
    {
        $cocody = $this->commune('Cocody', 9);
        $this->entreprise(['TITRE' => 'Étude de Maître Koffi, notaire', 'ACTIVITE' => 'Notaires', 'COMMUNE_ID' => $cocody->ID]);
        $this->entreprise(['TITRE' => 'Office notarial du Plateau', 'ACTIVITE' => 'Notaire, actes', 'COMMUNE_ID' => 13]);
        $this->entreprise(['TITRE' => 'Boulangerie du coin', 'ACTIVITE' => 'Pain']);

        $this->getJson('/api/v1/annuaire/recherche?q=notaire')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/annuaire/recherche?q=notaire&commune=cocody')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.commune.lien', 'cocody');
        $this->getJson('/api/v1/annuaire/recherche?q=Bo')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/annuaire/recherche')->assertUnprocessable()->assertJsonValidationErrors('q');
    }
}
