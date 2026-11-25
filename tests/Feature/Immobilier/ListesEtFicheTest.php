<?php

namespace Tests\Feature\Immobilier;

use App\Mail\Immobilier\NouveauMessageMail;
use App\Models\Statistique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ListesEtFicheTest extends TestCase
{
    use CreeDesBiens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerReferentiels();
    }

    public function test_l_accueil_immobilier_s_affiche(): void
    {
        $agence = $this->annonceur();
        $this->bien($agence, [], ['TITRE' => 'Villa avec piscine à Cocody']);

        $this->get('/immobilier')->assertOk()->assertSee('Villa avec piscine à Cocody');
    }

    public function test_la_liste_des_ventes_n_affiche_ni_locations_ni_biens_non_valides(): void
    {
        $agence = $this->annonceur();
        $this->bien($agence, [], ['TITRE' => 'Villa en vente visible']);
        $this->bien($agence, ['TYPE_IMMOBILIER_ID' => 1], ['TITRE' => 'Studio en location']);
        $this->bien($agence, ['VALIDATION' => 'NON'], ['TITRE' => 'Villa pas encore validee']);

        $this->get('/immobilier/achats')
            ->assertOk()
            ->assertSee('Villa en vente visible')
            ->assertDontSee('Studio en location')
            ->assertDontSee('Villa pas encore validee');

        $this->get('/immobilier/locations')->assertOk()->assertSee('Studio en location')->assertDontSee('Villa en vente visible');
    }

    public function test_les_filtres_commune_categorie_prix_et_chambres(): void
    {
        $agence = $this->annonceur();
        $this->bien($agence, ['PRIX' => '30000000', 'CHAMBRE' => 2], ['TITRE' => 'Bien Cocody pas cher']);
        $this->bien($agence, ['PRIX' => '900000000', 'CHAMBRE' => 6], ['TITRE' => 'Bien Cocody luxueux']);
        $this->bien($agence, ['CATEGORIE_IMMOBILIER_ID' => $this->appartement->ID], ['TITRE' => 'Appartement Yopougon', 'COMMUNE_ID' => $this->yopougon->ID]);

        $this->get('/immobilier/achats?commune='.$this->yopougon->ID)
            ->assertSee('Appartement Yopougon')->assertDontSee('Bien Cocody pas cher');

        $this->get('/immobilier/achats?prix_max=100%20000%20000')
            ->assertSee('Bien Cocody pas cher')->assertDontSee('Bien Cocody luxueux');

        $this->get('/immobilier/achats?chambres=4')
            ->assertSee('Bien Cocody luxueux')->assertDontSee('Bien Cocody pas cher');

        $this->get('/immobilier/achats?categorie='.$this->appartement->ID)
            ->assertSee('Appartement Yopougon')->assertDontSee('Bien Cocody luxueux');
    }

    public function test_la_fiche_compte_la_visite_pour_l_annonceur(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence, [], ['TITRE' => 'Duplex Riviera']);

        $this->get('/immobilier/proprietes-'.$bien->entreprise->LIEN)
            ->assertOk()
            ->assertSee('Duplex Riviera')
            ->assertSee('07 07 07 07 07')
            ->assertSee('wa.me/2250707070707', false);

        $this->assertSame(1, (int) $bien->entreprise->fresh()->COMPTEUR);
        $this->assertDatabaseHas('statistiques', ['ENTREPRISE_ID' => $agence->entreprise_id, 'TYPE_STAT' => Statistique::VUE, 'STATS' => 1]);
    }

    public function test_un_bien_non_valide_est_invisible_sauf_pour_son_annonceur(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence, ['VALIDATION' => 'NON'], ['TITRE' => 'Bien en attente']);
        $url = '/immobilier/proprietes-'.$bien->entreprise->LIEN;

        $this->get($url)->assertNotFound();
        $this->actingAs($this->annonceur('Autre agence'))->get($url)->assertNotFound();
        $this->actingAs($agence)->get($url)->assertOk()->assertSee('pas encore visible du public');
    }

    public function test_un_internaute_peut_ecrire_a_l_annonceur(): void
    {
        Mail::fake();
        $agence = $this->annonceur('Agence Contact');
        $bien = $this->bien($agence);

        $this->post('/immobilier/proprietes-'.$bien->entreprise->LIEN.'/contact', [
            'prenom' => 'Awa',
            'nom' => 'Koné',
            'telephone' => '07 08 09 10 11',
            'email' => 'awa@exemple.ci',
            'message' => 'Bonjour, ce bien est-il disponible ?',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_entreprises', [
            'entreprise_id' => $agence->entreprise_id,
            'email' => 'awa@exemple.ci',
            'emailEntreprise' => 'agence-contact@exemple.ci',
            'status' => 0,
        ]);
        Mail::assertSent(NouveauMessageMail::class, fn ($mail) => $mail->hasTo('agence-contact@exemple.ci'));
    }

    public function test_le_pot_de_miel_bloque_les_robots(): void
    {
        $bien = $this->bien($this->annonceur());

        $this->post('/immobilier/proprietes-'.$bien->entreprise->LIEN.'/contact', [
            'prenom' => 'Bot', 'nom' => 'Bot', 'telephone' => '0102030405', 'email' => 'bot@spam.io',
            'message' => 'Achetez nos produits miracles', 'site_web' => 'http://spam.io',
        ])->assertRedirect();

        $this->assertDatabaseCount('contact_entreprises', 0);
    }

    public function test_pages_annonceurs(): void
    {
        $agence = $this->annonceur('Isis Immobilier');
        $this->bien($agence, ['TYPE_IMMOBILIER_ID' => 1], ['TITRE' => 'Appartement chez Isis']);
        $promoteur = $this->annonceur('Promo Construct');
        $this->bien($promoteur, ['TYPE_IMMOBILIER_ID' => 3]);

        $this->get('/immobilier/agences')->assertOk()->assertSee('Isis Immobilier')->assertDontSee('Promo Construct');
        $this->get('/immobilier/promoteurs')->assertOk()->assertSee('Promo Construct');
        $this->get('/immobilier/annonceur/isis-immobilier')->assertOk()->assertSee('Appartement chez Isis');
        $this->get('/immobilier/annonceur/inconnu')->assertNotFound();
    }

    public function test_les_anciennes_urls_sont_redirigees(): void
    {
        $agence = $this->annonceur('Victoire Immobilier');

        $this->get('/immobilier/agence-'.$agence->entreprise_id)->assertRedirect('/immobilier/annonceur/victoire-immobilier')->assertStatus(301);
        $this->get('/immobilier-promoteur/'.$agence->id)->assertRedirect('/immobilier/annonceur/victoire-immobilier');
        $this->get('/immobilier/promoteurs-victoire-immobilier')->assertRedirect('/immobilier/annonceur/victoire-immobilier');
        $this->get('/immobilier/biens-en-vente?categorie=2&localisation=9&prix=50000000')
            ->assertStatus(301)->assertRedirect('/immobilier/achats?categorie=2&commune=9&prix_max=50000000');
        $this->get('/immobilier/all_lovations')->assertRedirect('/immobilier/locations');
        $this->get('/immobilier/Mes_Proprietes')->assertRedirect('/immobilier/espace-pro/biens');
        $this->get('/immobiliers/financement')->assertRedirect('/immobilier/financement');
    }
}
