<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\DatabaseTruncation;

/**
 * La recherche s'appuie sur l'index FULLTEXT de MySQL, qui ne voit que les données validées :
 * on utilise donc la troncature plutôt qu'une transaction annulée.
 */
class RechercheTest extends SiteTestCase
{
    use DatabaseTruncation;

    protected $tablesToTruncate = ['entreprises', 'annuaires', 'communes', 'sous_categorie', 'categorie_annuaires'];

    public function test_la_recherche_trouve_par_nom_sigle_ou_activite(): void
    {
        $this->entreprise(['TITRE' => 'Société Ivoirienne de Banque', 'SIGLE' => 'SIB', 'ACTIVITE' => 'Banque']);
        $this->entreprise(['TITRE' => 'Etude Maître Yao', 'ACTIVITE' => 'Notaire, actes notariés']);
        $this->entreprise(['TITRE' => 'Boulangerie du Plateau', 'ACTIVITE' => 'Pain']);

        $this->get('/annuaire-recherche?q=notaire')->assertOk()->assertSee('Etude Maître Yao')->assertDontSee('Boulangerie du Plateau');
        $this->get('/annuaire-recherche?q=banque')->assertOk()->assertSee('Société Ivoirienne de Banque');
        $this->get('/annuaire-recherche?q=SIB')->assertOk()->assertSee('Société Ivoirienne de Banque');
        $this->get('/annuaire-recherche?search_box=boulangerie')->assertOk()->assertSee('Boulangerie du Plateau');
    }

    public function test_la_recherche_filtre_par_commune_et_propose_les_rubriques(): void
    {
        $cocody = $this->commune('Cocody');
        $yop = $this->commune('Yopougon');
        $this->rubrique('Santé', 'Pharmacies');
        $this->entreprise(['TITRE' => 'Pharmacie Riviera', 'COMMUNE_ID' => $cocody->ID]);
        $this->entreprise(['TITRE' => 'Pharmacie Siporex', 'COMMUNE_ID' => $yop->ID]);

        $this->get('/annuaire-recherche?q=pharmacie&commune=yopougon')
            ->assertOk()
            ->assertSee('Pharmacie Siporex')
            ->assertDontSee('Pharmacie Riviera')
            ->assertSee('Rubriques correspondantes');
    }

    public function test_un_terme_trop_court_n_execute_pas_de_recherche(): void
    {
        $this->get('/annuaire-recherche?q=a')->assertOk()->assertSee('Tapez au moins deux caractères');
    }
}
