<?php

namespace Tests\Feature\Site;

use App\Models\ContactEntreprise;
use App\Models\Entreprise;
use App\Models\PhotoAnnuaire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnnuaireTest extends SiteTestCase
{
    use RefreshDatabase;

    public function test_l_accueil_de_l_annuaire_liste_les_categories_avec_leur_nombre(): void
    {
        [$cat, $sous] = $this->rubrique('Santé', 'Pharmacies');
        $this->classer($this->entreprise(), $cat, $sous);

        $this->get('/annuaire')->assertOk()->assertSee('Santé')->assertSee('1 entreprises');
    }

    public function test_une_categorie_liste_ses_rubriques(): void
    {
        [$cat, $sous] = $this->rubrique('Santé', 'Pharmacies');
        $this->classer($this->entreprise(), $cat, $sous);

        $this->get('/annuaire/sante')->assertOk()->assertSee('Pharmacies')->assertSee('/annuaire/sante/pharmacies', false);
        $this->get('/annuaire/inconnue')->assertNotFound();
    }

    public function test_une_rubrique_affiche_les_clients_en_premier_et_filtre_par_commune(): void
    {
        $cocody = $this->commune('Cocody');
        $plateau = $this->commune('Plateau');
        [$cat, $sous] = $this->rubrique();
        $standard = $this->entreprise(['TITRE' => 'AAA Pharmacie Standard', 'COMMUNE_ID' => $cocody->ID]);
        $client = $this->entreprise(['TITRE' => 'ZZZ Pharmacie Cliente', 'COMMUNE_ID' => $plateau->ID], ['CLIENT' => 'OUI', 'VIP' => 1]);
        $this->classer($standard, $cat, $sous);
        $this->classer($client, $cat, $sous);

        $this->get('/annuaire/sante/pharmacies')
            ->assertOk()
            ->assertSeeInOrder(['ZZZ Pharmacie Cliente', 'AAA Pharmacie Standard']);

        $this->get('/annuaire/sante/pharmacies?commune=cocody')
            ->assertOk()
            ->assertSee('AAA Pharmacie Standard')
            ->assertDontSee('ZZZ Pharmacie Cliente');
    }

    public function test_la_fiche_entreprise_s_affiche_et_compte_la_visite(): void
    {
        $e = $this->entreprise(['TITRE' => 'Cabinet Kouamé', 'LIEN' => 'cabinet-kouame'], ['DESCRIPTION' => '<p style="color:red">Conseil <script>alert(1)</script>fiscal</p>', 'LATITUDE' => 5.35, 'LONGITUDE' => -4.0]);
        PhotoAnnuaire::create(['ANNUAIRE_ID' => $e->annuaire()->value('ID'), 'IMAGE' => 'photo.jpg']);

        $this->get('/details/cabinet-kouame')
            ->assertOk()
            ->assertSee('Cabinet Kouamé')
            ->assertSee('27 22 00 00 00')
            ->assertSee('<p>Conseil fiscal</p>', false)
            ->assertDontSee('alert(1)')
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertSee('pratikMap', false);

        $this->assertSame(1.0, (float) Entreprise::find($e->ID)->COMPTEUR);
    }

    public function test_un_lien_historique_avec_slash_et_esperluette_fonctionne(): void
    {
        $this->entreprise(['TITRE' => 'Douanes', 'LIEN' => 'direction-des-douanes/-service-x']);
        $this->entreprise(['TITRE' => 'Dako & associés', 'LIEN' => 'maitre-dako-&-associes']);

        $this->get('/details/direction-des-douanes/-service-x')->assertOk()->assertSee('Douanes');
        $this->get('/details/maitre-dako-%26-associes')->assertOk()->assertSee('Dako');
        $this->get('/details/inconnue')->assertNotFound();
    }

    public function test_le_formulaire_de_contact_transmet_le_message_a_l_entreprise(): void
    {
        $e = $this->entreprise(['TITRE' => 'Clinique Test']);

        $this->from('/details/'.$e->LIEN)
            ->post(route('annuaire.contact', $e->ID), $this->humain([
                'nom' => 'Koné', 'prenom' => 'Awa', 'contact' => '0700000000', 'email' => 'awa@exemple.ci',
                'objet' => 'Rendez-vous', 'message' => 'Bonjour, êtes-vous ouverts samedi ?',
            ]))
            ->assertRedirect('/details/'.$e->LIEN)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_entreprises', ['entreprise_id' => $e->ID, 'email' => 'awa@exemple.ci', 'emailEntreprise' => 'contact@test.ci', 'status' => 0]);
    }

    public function test_un_robot_qui_remplit_le_champ_piege_n_enregistre_rien(): void
    {
        $e = $this->entreprise();

        $this->post(route('annuaire.contact', $e->ID), $this->humain([
            'nom' => 'Bot', 'contact' => '1', 'email' => 'bot@spam.com', 'objet' => 'Gagnez', 'message' => 'Cliquez ici',
            'site_internet' => 'https://spam.example',
        ]))->assertSessionHas('success');

        $this->assertSame(0, ContactEntreprise::count());
    }
}
