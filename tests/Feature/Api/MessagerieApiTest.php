<?php

namespace Tests\Feature\Api;

use App\Mail\Immobilier\NouveauMessageMail;
use App\Mail\Immobilier\ReponseMessageMail;
use App\Models\Annuaire;
use App\Models\ContactEntreprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class MessagerieApiTest extends ApiTestCase
{
    use RefreshDatabase;

    private function donnees(array $extra = []): array
    {
        return $extra + ['prenom' => 'Awa', 'nom' => 'Koné', 'telephone' => '07 08 09 10 11', 'email' => 'awa@exemple.ci', 'message' => 'Bonjour, ce bien est-il toujours disponible ?'];
    }

    public function test_message_a_l_annonceur_puis_lecture_et_reponse_dans_l_espace_pro(): void
    {
        Mail::fake();
        $pro = $this->pro();
        $bien = $this->bien($pro);

        $this->postJson('/api/v1/immobilier/biens/'.$bien->entreprise->LIEN.'/contact', $this->donnees())->assertCreated();

        $message = ContactEntreprise::firstOrFail();
        $this->assertSame(0, (int) $message->status);

        $this->getJson('/api/v1/pro/messages?statut=non_lus', $this->enTetes($pro))->assertOk()->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/pro/messages/'.$message->id, $this->enTetes($pro))->assertOk()
            ->assertJsonPath('data.nom', 'Awa Koné')
            ->assertJsonPath('data.lu', true);

        $this->postJson('/api/v1/pro/messages/'.$message->id.'/repondre', ['reponse' => 'Oui, visite possible samedi.'], $this->enTetes($pro))
            ->assertOk()
            ->assertJsonPath('data.repondu', true)
            ->assertJsonPath('email_envoye', true);

        Mail::assertSent(ReponseMessageMail::class, fn ($m) => $m->hasTo('awa@exemple.ci'));
        $this->getJson('/api/v1/pro/messages?statut=non_lus', $this->enTetes($pro))->assertJsonCount(0, 'data');
    }

    public function test_un_annonceur_ne_lit_pas_les_messages_d_un_autre(): void
    {
        Mail::fake();
        $bien = $this->bien($this->pro());
        $this->postJson('/api/v1/immobilier/biens/'.$bien->entreprise->LIEN.'/contact', $this->donnees())->assertCreated();

        $autre = $this->pro(['email' => 'autre@exemple.ci']);
        $this->getJson('/api/v1/pro/messages/'.ContactEntreprise::firstOrFail()->id, $this->enTetes($autre))->assertForbidden();
    }

    public function test_message_a_une_entreprise_de_l_annuaire(): void
    {
        Mail::fake();
        $entreprise = $this->entreprise(['TITRE' => 'Cabinet Kouamé', 'LIEN' => 'cabinet-kouame'], ['EMAIL_1' => 'cabinet@exemple.ci']);

        $this->postJson('/api/v1/annuaire/entreprises/cabinet-kouame/contact', $this->donnees(['objet' => 'Rendez-vous']))
            ->assertCreated()
            ->assertJsonPath('message', 'Votre message a bien été transmis à Cabinet Kouamé.');

        $this->assertDatabaseHas('contact_entreprises', ['entreprise_id' => $entreprise->ID, 'objet' => 'Rendez-vous', 'emailEntreprise' => 'cabinet@exemple.ci']);
    }

    public function test_validation_du_message(): void
    {
        $bien = $this->bien($this->pro());

        $this->postJson('/api/v1/immobilier/biens/'.$bien->entreprise->LIEN.'/contact', $this->donnees(['telephone' => 'abc', 'message' => 'court']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['telephone', 'message']);
    }

    public function test_succursales_sur_la_fiche_entreprise(): void
    {
        $siege = $this->entreprise(['TITRE' => 'Banque Siège', 'LIEN' => 'banque-siege']);
        $agence = $this->entreprise(['TITRE' => 'Banque Agence Yopougon', 'LIEN' => 'banque-agence-yopougon']);
        Annuaire::where('ENTREPRISE_ID', $agence->ID)->update(['SIEGE_ID' => $siege->ID, 'TEL_1' => '27 23 00 00 00']);

        $this->getJson('/api/v1/annuaire/entreprises/banque-siege')->assertOk()
            ->assertJsonPath('data.succursales.0.titre', 'Banque Agence Yopougon')
            ->assertJsonPath('data.succursales.0.telephone', '27 23 00 00 00');
    }

    public function test_mot_de_passe_oublie_repond_meme_si_l_e_mail_ne_part_pas(): void
    {
        Password::shouldReceive('sendResetLink')->andThrow(new \RuntimeException('SMTP indisponible'));

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'x@exemple.ci'])->assertOk();
    }
}
