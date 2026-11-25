<?php

namespace Tests\Feature\Api;

use App\Models\CategorieImmobilier;
use App\Models\ContactEntreprise;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\PhotoImmobilier;
use App\Models\Statistique;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class ProApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_n_a_pas_acces_a_l_espace_pro(): void
    {
        $this->getJson('/api/v1/pro/biens', $this->enTetes($this->utilisateur()))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/pro/biens')->assertUnauthorized();
    }

    public function test_creer_un_bien_avec_photos(): void
    {
        $pro = $this->pro();
        $commune = $this->commune('Cocody', 9);
        $categorie = CategorieImmobilier::create(['DESIGNATION' => 'Villa duplex', 'ACTIVATION' => 1]);

        $reponse = $this->post('/api/v1/pro/biens', [
            'titre' => 'Villa duplex 5 pièces à Angré',
            'type_id' => 2,
            'categorie_id' => $categorie->ID,
            'commune_id' => $commune->ID,
            'prix' => '120 000 000',
            'chambres' => 4,
            'equipements' => ['piscine', 'garage'],
            'illustration' => UploadedFile::fake()->image('facade.jpg', 1200, 800),
            'photos' => [UploadedFile::fake()->image('salon.jpg'), UploadedFile::fake()->image('cuisine.png')],
        ], $this->enTetes($pro));

        $reponse->assertCreated()
            ->assertJsonPath('data.statut', 'en_attente')
            ->assertJsonPath('data.prix', 120000000)
            ->assertJsonPath('data.equipements.piscine', true)
            ->assertJsonPath('data.equipements.jardin', false)
            ->assertJsonCount(2, 'data.photos')
            ->assertJsonStructure(['message']);

        $bien = Immobilier::with('entreprise', 'photos')->findOrFail($reponse->json('data.id'));
        $this->assertSame('NON', $bien->VALIDATION);
        $this->assertSame($pro->id, (int) $bien->UTILISATEUR);
        $this->assertSame('villa-duplex-5-pieces-a-angre-'.$bien->ENTREPRISE_ID, $bien->entreprise->LIEN);
        $this->assertStringStartsWith('ILL', $bien->ILLUSTATION);
        $this->assertFileExists(Media::path('immobilier', $bien->ILLUSTATION));
        $bien->photos->each(fn ($p) => $this->assertFileExists(Media::path('immobilier', $p->IMAGE)));
    }

    public function test_validation_des_champs_obligatoires(): void
    {
        $this->postJson('/api/v1/pro/biens', ['titre' => 'abc'], $this->enTetes($this->pro()))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titre', 'type_id', 'categorie_id', 'commune_id']);
    }

    public function test_modifier_son_bien_remplace_l_ancienne_photo(): void
    {
        $pro = $this->pro();
        $bien = $this->bien($pro, ['ILLUSTATION' => $this->fauxFichier('immobilier', 'ancienne.jpg')], ['TITRE' => 'Ancien titre']);

        $this->post("/api/v1/pro/biens/{$bien->ID}", [
            '_method' => 'PUT',
            'titre' => 'Nouveau titre du bien',
            'prix' => '75000000',
            'illustration' => UploadedFile::fake()->image('nouvelle.jpg'),
        ], $this->enTetes($pro))
            ->assertOk()
            ->assertJsonPath('data.titre', 'Nouveau titre du bien')
            ->assertJsonPath('data.lien', 'nouveau-titre-du-bien-'.$bien->ENTREPRISE_ID)
            ->assertJsonPath('data.prix', 75000000);

        $this->assertFileDoesNotExist(Media::path('immobilier', 'ancienne.jpg'));
        $this->assertFileExists(Media::path('immobilier', Immobilier::find($bien->ID)->ILLUSTATION));
    }

    public function test_impossible_de_toucher_au_bien_d_un_autre(): void
    {
        $proprietaire = $this->pro();
        $intrus = $this->pro();
        $bien = $this->bien($proprietaire);
        $entetes = $this->enTetes($intrus);

        $this->getJson("/api/v1/pro/biens/{$bien->ID}", $entetes)->assertForbidden();
        $this->putJson("/api/v1/pro/biens/{$bien->ID}", ['titre' => 'Piraté !!'], $entetes)->assertForbidden();
        $this->deleteJson("/api/v1/pro/biens/{$bien->ID}", [], $entetes)->assertForbidden();
        $this->assertDatabaseHas('immobilier', ['ID' => $bien->ID]);
    }

    public function test_supprimer_une_photo_de_la_galerie_efface_le_fichier(): void
    {
        $pro = $this->pro();
        $bien = $this->bien($pro);
        $photo = PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'galerie.jpg')]);

        $this->deleteJson("/api/v1/pro/biens/{$bien->ID}/photos/{$photo->ID}", [], $this->enTetes($pro))->assertNoContent();

        $this->assertDatabaseMissing('photo_immobilier', ['ID' => $photo->ID]);
        $this->assertFileDoesNotExist(Media::path('immobilier', 'galerie.jpg'));
    }

    public function test_supprimer_un_bien_efface_fiche_et_fichiers(): void
    {
        $pro = $this->pro();
        $bien = $this->bien($pro, ['ILLUSTATION' => $this->fauxFichier('immobilier', 'principale.jpg')]);
        PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'p1.jpg')]);

        $this->deleteJson("/api/v1/pro/biens/{$bien->ID}", [], $this->enTetes($pro))->assertNoContent();

        $this->assertDatabaseMissing('immobilier', ['ID' => $bien->ID]);
        $this->assertNull(Entreprise::find($bien->ENTREPRISE_ID));
        $this->assertFileDoesNotExist(Media::path('immobilier', 'principale.jpg'));
        $this->assertFileDoesNotExist(Media::path('immobilier', 'p1.jpg'));
    }

    public function test_liste_de_mes_biens_par_statut(): void
    {
        $pro = $this->pro();
        $this->bien($pro);
        $this->bien($pro, ['VALIDATION' => 'NON']);
        $this->bien($this->pro());

        $entetes = $this->enTetes($pro);
        $this->getJson('/api/v1/pro/biens', $entetes)->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/pro/biens?statut=en_attente', $entetes)->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_statistiques_et_messages(): void
    {
        $pro = $this->pro();
        $this->bien($pro);
        Statistique::enregistrer($pro->entreprise_id, Statistique::VUE);
        Statistique::enregistrer($pro->entreprise_id, Statistique::VUE);
        Statistique::enregistrer($pro->entreprise_id, Statistique::APPEL);
        ContactEntreprise::create(['entreprise_id' => $pro->entreprise_id, 'nom' => 'Yao', 'prenom' => 'Ama', 'contact' => '0700000000', 'email' => 'ama@example.ci', 'objet' => 'Visite', 'contactmessage' => 'Disponible samedi ?', 'status' => 0]);
        ContactEntreprise::create(['entreprise_id' => 999, 'nom' => 'Autre', 'prenom' => 'X', 'contact' => '0', 'email' => 'x@example.ci', 'objet' => 'Autre', 'status' => 0]);

        $entetes = $this->enTetes($pro);
        $this->getJson('/api/v1/pro/statistiques', $entetes)
            ->assertOk()
            ->assertJsonPath('data.mois_en_cours.vues', 2)
            ->assertJsonPath('data.mois_en_cours.appels', 1)
            ->assertJsonPath('data.total_annee.vues', 2)
            ->assertJsonPath('data.biens.publies', 1)
            ->assertJsonCount(12, 'data.par_mois');

        $this->getJson('/api/v1/pro/messages', $entetes)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nom', 'Ama Yao')
            ->assertJsonPath('data.0.lu', false)
            ->assertJsonPath('meta.non_lus', 1);
    }
}
