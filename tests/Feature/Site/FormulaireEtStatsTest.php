<?php

namespace Tests\Feature\Site;

use App\Models\Contact;
use App\Models\Statistique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

class FormulaireEtStatsTest extends SiteTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // "contacts" est une table MyISAM héritée : les transactions de test ne l'annulent pas.
        Contact::query()->delete();
    }

    public function test_le_formulaire_de_contact_enregistre_le_message(): void
    {
        $this->post('/contact', $this->humain(['nom' => 'Yao', 'email' => 'yao@exemple.ci', 'objet' => 'Correction de fiche', 'message' => 'Notre numéro a changé.']))
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', ['email' => 'yao@exemple.ci', 'categorie' => 'Contact', 'objet' => 'Correction de fiche']);
    }

    public function test_le_spam_n_est_pas_enregistre(): void
    {
        // Champ piège rempli
        $this->post('/contact', $this->humain(['nom' => 'Bot', 'email' => 'b@spam.com', 'objet' => 'x', 'message' => 'y', 'site_internet' => 'spam']))->assertSessionHas('success');
        // Formulaire envoyé en moins de 3 secondes
        $this->post('/contact', ['nom' => 'Bot', 'email' => 'b@spam.com', 'objet' => 'x', 'message' => 'y', '_horodatage' => Crypt::encryptString((string) time())])->assertSessionHas('success');
        // Horodatage absent ou falsifié
        $this->post('/contact', ['nom' => 'Bot', 'email' => 'b@spam.com', 'objet' => 'x', 'message' => 'y', '_horodatage' => 'faux'])->assertSessionHas('success');

        $this->assertSame(0, Contact::count());
    }

    public function test_la_souscription_est_enregistree_pour_etre_rappelee(): void
    {
        $this->get('/se-faire-connaitre')->assertOk();

        $this->post('/se-faire-connaitre', $this->humain([
            'entreprise' => 'Quincaillerie Bon Prix', 'activite' => 'Quincaillerie', 'commune' => 'Abobo',
            'nom' => 'Traoré', 'telephone' => '0102030405', 'email' => 'tb@exemple.ci', 'formule' => 'pro',
        ]))->assertRedirect(route('souscription'));

        $contact = Contact::firstOrFail();
        $this->assertSame('Souscription', $contact->categorie);
        $this->assertStringContainsString('Quincaillerie Bon Prix', $contact->objet);
        $this->assertStringContainsString('Pro', $contact->message);
    }

    public function test_les_clics_appel_et_email_sont_comptes(): void
    {
        $e = $this->entreprise();

        $this->post(route('stats.appel', $e->ID))->assertNoContent();
        $this->post(route('stats.appel', $e->ID))->assertNoContent();
        $this->post(route('stats.email', $e->ID))->assertNoContent();
        $this->post(route('stats.appel', 999999))->assertNoContent();

        $this->assertSame(2, (int) Statistique::where('ENTREPRISE_ID', $e->ID)->where('TYPE_STAT', Statistique::APPEL)->value('STATS'));
        $this->assertSame(1, (int) Statistique::where('ENTREPRISE_ID', $e->ID)->where('TYPE_STAT', Statistique::EMAIL)->value('STATS'));
        $this->assertSame(0, Statistique::where('ENTREPRISE_ID', 999999)->count());
    }

    public function test_pages_legales_et_papiers_administratifs(): void
    {
        $this->get('/conditions-utilisation')->assertOk()->assertSee('Droit applicable');
        $this->get('/regles-de-confidentialite')->assertOk()->assertSee('Données de localisation');
        $this->get('/papier-administratif')->assertOk();
        $this->get('/papier-administratif/commune-inconnue')->assertNotFound();
    }
}
