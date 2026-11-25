<?php

namespace Tests\Feature\Immobilier;

use App\Mail\Immobilier\ReponseMessageMail;
use App\Models\ContactEntreprise;
use App\Models\Immobilier;
use App\Models\PhotoImmobilier;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EspaceProTest extends TestCase
{
    use CreeDesBiens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preparerReferentiels();
    }

    private function donneesBien(array $surcharges = []): array
    {
        return $surcharges + [
            'titre' => 'Villa duplex 5 pièces à Cocody Angré',
            'type_immobilier_id' => 2,
            'categorie_immobilier_id' => $this->villa->ID,
            'commune_id' => $this->cocody->ID,
            'prix' => '125 000 000',
            'chambre' => 4,
            'salle_bain' => 3,
            'surface' => 350,
            'latitude' => '5,3912',
            'longitude' => '-3.9876',
            'equipements' => ['PISCINE', 'GARAGE'],
            'description' => 'Belle villa.',
        ];
    }

    public function test_l_espace_pro_est_reserve_aux_annonceurs(): void
    {
        $this->get('/immobilier/espace-pro')->assertRedirect('/immobilier/connexion');

        $visiteur = User::create(['name' => 'Visiteur', 'email' => 'v@exemple.ci', 'password' => 'secret-secret', 'role_id' => 7, 'active' => 1]);
        $this->actingAs($visiteur)->get('/immobilier/espace-pro')->assertRedirect('/immobilier/connexion');

        $this->actingAs($this->annonceur())->get('/immobilier/espace-pro')->assertOk()->assertSee('Annonces en ligne');
    }

    public function test_toutes_les_pages_de_l_espace_pro_s_affichent(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence);
        $message = ContactEntreprise::create(['entreprise_id' => $agence->entreprise_id, 'nom' => 'K', 'prenom' => 'A', 'contact' => '0707070707', 'email' => 'a@b.ci', 'objet' => 'Objet', 'contactmessage' => 'Bonjour']);

        $this->actingAs($agence);
        foreach (['/immobilier/espace-pro/biens', '/immobilier/espace-pro/biens/nouveau', '/immobilier/espace-pro/biens/'.$bien->ID.'/modifier', '/immobilier/espace-pro/profil', '/immobilier/espace-pro/messages', '/immobilier/espace-pro/messages/'.$message->id, '/immobilier/espace-pro/statistiques'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_un_annonceur_depose_un_bien_avec_photos(): void
    {
        $agence = $this->annonceur();

        $reponse = $this->actingAs($agence)->post('/immobilier/espace-pro/biens', $this->donneesBien([
            'illustration' => UploadedFile::fake()->image('facade.jpg', 1200, 800),
            'photos' => [UploadedFile::fake()->image('salon.jpg'), UploadedFile::fake()->image('cuisine.png')],
        ]));

        $bien = Immobilier::with('entreprise', 'photos')->firstOrFail();
        $reponse->assertRedirect('/immobilier/espace-pro/biens/'.$bien->ID.'/modifier');

        $this->assertSame('NON', $bien->VALIDATION, 'un nouveau bien attend la validation de l\'admin');
        $this->assertSame('125000000', $bien->PRIX);
        $this->assertSame('OUI', $bien->PISCINE);
        $this->assertNull($bien->JARDIN);
        $this->assertSame('5.3912', $bien->LATITUDE);
        $this->assertSame('villa-duplex-5-pieces-a-cocody-angre-'.$bien->entreprise->ID, $bien->entreprise->LIEN);
        $this->assertSame(3, (int) $bien->entreprise->TYPE_ID);
        $this->assertStringStartsWith('ILL', $bien->ILLUSTATION);
        $this->assertFileExists(Media::path('immobilier', $bien->ILLUSTATION));
        $this->assertCount(2, $bien->photos);
        $bien->photos->each(fn ($p) => $this->assertFileExists(Media::path('immobilier', $p->IMAGE)));
    }

    public function test_la_validation_refuse_un_formulaire_incomplet(): void
    {
        $this->actingAs($this->annonceur())
            ->post('/immobilier/espace-pro/biens', ['titre' => 'Court'])
            ->assertSessionHasErrors(['titre', 'type_immobilier_id', 'categorie_immobilier_id', 'commune_id']);

        $this->assertDatabaseCount('immobilier', 0);
    }

    public function test_remplacer_la_photo_principale_supprime_l_ancienne(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence, ['ILLUSTATION' => $this->fauxFichier('immobilier', 'ancienne.jpg')]);

        $this->actingAs($agence)->put('/immobilier/espace-pro/biens/'.$bien->ID, $this->donneesBien([
            'illustration' => UploadedFile::fake()->image('nouvelle.jpg'),
        ]))->assertRedirect();

        $this->assertFileDoesNotExist(Media::path('immobilier', 'ancienne.jpg'));
        $this->assertFileExists(Media::path('immobilier', $bien->fresh()->ILLUSTATION));
        $this->assertSame($bien->entreprise->LIEN, $bien->entreprise->fresh()->LIEN, 'le lien SEO ne change pas');
    }

    public function test_un_annonceur_ne_peut_pas_toucher_au_bien_d_un_autre(): void
    {
        $proprietaire = $this->annonceur('Agence A');
        $intrus = $this->annonceur('Agence B');
        $bien = $this->bien($proprietaire);
        $photo = PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'p.jpg')]);

        $this->actingAs($intrus);
        $this->get('/immobilier/espace-pro/biens/'.$bien->ID.'/modifier')->assertForbidden();
        $this->put('/immobilier/espace-pro/biens/'.$bien->ID, $this->donneesBien())->assertForbidden();
        $this->delete('/immobilier/espace-pro/biens/'.$bien->ID)->assertForbidden();
        $this->delete('/immobilier/espace-pro/biens/'.$bien->ID.'/photos/'.$photo->ID)->assertForbidden();

        $this->assertDatabaseHas('immobilier', ['ID' => $bien->ID]);
        $this->assertFileExists(Media::path('immobilier', 'p.jpg'));
    }

    public function test_supprimer_une_photo_de_la_galerie(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence);
        $photo = PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'galerie.jpg')]);

        $this->actingAs($agence)->delete('/immobilier/espace-pro/biens/'.$bien->ID.'/photos/'.$photo->ID)->assertRedirect();

        $this->assertDatabaseMissing('photo_immobilier', ['ID' => $photo->ID]);
        $this->assertFileDoesNotExist(Media::path('immobilier', 'galerie.jpg'));
    }

    public function test_supprimer_un_bien_efface_fiche_et_fichiers(): void
    {
        $agence = $this->annonceur();
        $bien = $this->bien($agence, ['ILLUSTATION' => $this->fauxFichier('immobilier', 'ill.jpg')]);
        PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'g1.jpg')]);

        $this->actingAs($agence)->delete('/immobilier/espace-pro/biens/'.$bien->ID)->assertRedirect('/immobilier/espace-pro/biens');

        $this->assertDatabaseMissing('immobilier', ['ID' => $bien->ID]);
        $this->assertDatabaseMissing('entreprises', ['ID' => $bien->ENTREPRISE_ID]);
        $this->assertFileDoesNotExist(Media::path('immobilier', 'ill.jpg'));
        $this->assertFileDoesNotExist(Media::path('immobilier', 'g1.jpg'));
    }

    public function test_messages_lecture_et_reponse(): void
    {
        Mail::fake();
        $agence = $this->annonceur();
        $autre = $this->annonceur('Autre');
        $message = ContactEntreprise::create(['entreprise_id' => $agence->entreprise_id, 'nom' => 'Koné', 'prenom' => 'Awa', 'contact' => '0707070707', 'email' => 'awa@exemple.ci', 'objet' => 'Votre annonce', 'contactmessage' => 'Disponible ?']);

        $this->actingAs($autre)->get('/immobilier/espace-pro/messages/'.$message->id)->assertForbidden();

        $this->actingAs($agence)->get('/immobilier/espace-pro/messages/'.$message->id)->assertOk()->assertSee('Disponible ?');
        $this->assertSame(1, (int) $message->fresh()->status);

        $this->post('/immobilier/espace-pro/messages/'.$message->id.'/reponse', ['reponse' => 'Oui, visite possible samedi.'])->assertRedirect('/immobilier/espace-pro/messages');
        $this->assertSame(2, (int) $message->fresh()->status);
        $this->assertSame('Oui, visite possible samedi.', $message->fresh()->reponse);
        Mail::assertSent(ReponseMessageMail::class, fn ($mail) => $mail->hasTo('awa@exemple.ci'));
    }

    public function test_mise_a_jour_du_profil_et_du_logo(): void
    {
        $agence = $this->annonceur();
        $agence->entreprise->annuaire->update(['LOGO' => $this->fauxFichier('logos', 'vieux-logo.png')]);

        $this->actingAs($agence)->put('/immobilier/espace-pro/profil', [
            'name' => 'Compte agence',
            'titre' => 'Agence Test Renommée',
            'commune_id' => $this->yopougon->ID,
            'cel_1' => '05 05 05 05 05',
            'email_1' => 'contact@agence.ci',
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
        ])->assertRedirect();

        $annuaire = $agence->entreprise->annuaire->fresh();
        $this->assertSame('05 05 05 05 05', $annuaire->CEL_1);
        $this->assertSame('Agence Test Renommée', $agence->entreprise->fresh()->TITRE);
        $this->assertFileDoesNotExist(Media::path('logos', 'vieux-logo.png'));
        $this->assertFileExists(Media::path('logos', $annuaire->LOGO));
    }
}
