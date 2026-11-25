<?php

namespace Tests\Feature\Immobilier;

use App\Models\ContactEntreprise;
use App\Models\Statistique;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Un annonceur doit retrouver dans son espace les vues et les messages de ses biens,
 * qu'il soit rattaché à une société ou non (cas des comptes créés sur l'ancien site).
 */
class SuiviAnnonceurTest extends TestCase
{
    use CreeDesBiens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerReferentiels();
        Mail::fake();
    }

    private function annonceurSansSociete(): User
    {
        return User::create([
            'name' => 'Cédrik Test', 'email' => 'cedrik@exemple.ci', 'password' => 'secret-secret',
            'role_id' => 5, 'CATEGORIE' => 'Immobilier', 'entreprise_id' => null, 'active' => 1,
        ]);
    }

    private function ecrire(string $lien): void
    {
        $this->post('/immobilier/proprietes-'.$lien.'/contact', [
            'prenom' => 'Awa', 'nom' => 'Koné', 'telephone' => '07 08 09 10 11',
            'email' => 'awa@exemple.ci', 'message' => 'Bonjour, ce bien est-il disponible ?',
        ])->assertRedirect();
    }

    public function test_annonceur_sans_societe_voit_messages_et_vues_de_ses_biens(): void
    {
        $annonceur = $this->annonceurSansSociete();
        $bien = $this->bien($annonceur, [], ['TITRE' => 'Maison a vendre test']);
        $lien = $bien->entreprise->LIEN;

        $this->get('/immobilier/proprietes-'.$lien)->assertOk()->assertSee('1 vue');
        $this->get('/immobilier/proprietes-'.$lien)->assertOk()->assertSee('2 vues');
        $this->ecrire($lien);

        $message = ContactEntreprise::firstOrFail();

        $this->actingAs($annonceur);
        $this->get('/immobilier/espace-pro')->assertOk()->assertSee('Awa')->assertSee('Koné');
        $this->get('/immobilier/espace-pro/messages')->assertOk()->assertSee('Koné');
        $this->get('/immobilier/espace-pro/messages/'.$message->id)->assertOk()->assertSee('ce bien est-il disponible');
        $this->get('/immobilier/espace-pro/statistiques')->assertOk()->assertSee('Maison a vendre test');

        $this->assertSame(2, (int) Statistique::where('TYPE_STAT', Statistique::VUE)->sum('STATS'));
    }

    public function test_annonceur_avec_societe_voit_aussi_ses_messages(): void
    {
        $agence = $this->annonceur('Agence Suivie');
        $bien = $this->bien($agence);

        $this->ecrire($bien->entreprise->LIEN);

        $this->actingAs($agence)->get('/immobilier/espace-pro/messages')->assertOk()->assertSee('Koné');
    }

    public function test_un_annonceur_ne_peut_pas_lire_le_message_d_un_autre(): void
    {
        $autre = $this->annonceur('Agence Voisine');
        $bienAutre = $this->bien($autre);
        $this->ecrire($bienAutre->entreprise->LIEN);

        $moi = $this->annonceurSansSociete();
        $this->bien($moi);

        $this->actingAs($moi)
            ->get('/immobilier/espace-pro/messages/'.ContactEntreprise::firstOrFail()->id)
            ->assertForbidden();
    }

    public function test_statistiques_par_periode(): void
    {
        $agence = $this->annonceur('Agence Periode');
        $this->bien($agence);
        $societe = $agence->entreprise_id;
        $annee = now()->year;

        Statistique::create(['ENTREPRISE_ID' => $societe, 'TYPE_STAT' => Statistique::VUE, 'MOIS' => '1', 'ANNEE' => (string) $annee, 'STATS' => 40]);
        Statistique::create(['ENTREPRISE_ID' => $societe, 'TYPE_STAT' => Statistique::APPEL, 'MOIS' => '1', 'ANNEE' => (string) $annee, 'STATS' => 7]);
        Statistique::create(['ENTREPRISE_ID' => $societe, 'TYPE_STAT' => Statistique::VUE, 'MOIS' => '3', 'ANNEE' => (string) ($annee - 1), 'STATS' => 900]);

        $this->actingAs($agence);

        $this->get('/immobilier/espace-pro/statistiques?annee='.$annee.'&mois=1')
            ->assertOk()
            ->assertViewHas('totaux', ['vues' => 40, 'appels' => 7, 'emails' => 0]);

        $this->get('/immobilier/espace-pro/statistiques?annee='.($annee - 1))
            ->assertOk()
            ->assertViewHas('totaux', ['vues' => 900, 'appels' => 0, 'emails' => 0])
            ->assertSee((string) ($annee - 1));
    }

    public function test_la_page_agence_affiche_vues_et_nombre_de_biens(): void
    {
        $agence = $this->annonceur('Agence Vitrine');
        $this->bien($agence);
        $this->bien($agence);

        $lien = $agence->entreprise->LIEN;

        $this->get('/immobilier/annonceur/'.$lien)->assertOk()->assertSee('2 biens en ligne')->assertSee('1 vue');
    }
}
