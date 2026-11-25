<?php

namespace Tests\Feature\Admin;

use App\Models\Annuaire;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\PhotoAnnuaire;
use App\Models\SousCategorie;
use App\Models\TypeEntreprise;
use App\Support\Media;
use Illuminate\Http\UploadedFile;

class EntreprisesTest extends AdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TypeEntreprise::forceCreate(['ID' => 1, 'DESIGNATION' => 'Annuaire', 'ACTIVATION' => 1]);
    }

    public function test_creer_puis_remplacer_le_logo_supprime_l_ancien_fichier(): void
    {
        $admin = $this->admin();
        $commune = Commune::create(['DESIGNATION' => 'Plateau', 'LIEN' => 'plateau']);

        $this->actingAs($admin)->post(route('admin.entreprises.store'), [
            'titre' => 'Cabinet Kouassi & Associés',
            'type_id' => 1,
            'commune_id' => $commune->ID,
            'client' => 'OUI',
            'activation' => 1,
            'tel_1' => '27 20 00 00 00',
            'email_1' => 'contact@kouassi.ci',
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertRedirect();

        $entreprise = Entreprise::where('TITRE', 'Cabinet Kouassi & Associés')->firstOrFail();
        $annuaire = Annuaire::where('ENTREPRISE_ID', $entreprise->ID)->firstOrFail();
        $this->assertSame('cabinet-kouassi-associes-'.$entreprise->ID, $entreprise->LIEN);
        $this->assertSame('OUI', $annuaire->CLIENT);
        $ancien = $annuaire->LOGO;
        $this->assertFileExists(Media::path('logos', $ancien));

        $this->actingAs($admin)->put(route('admin.entreprises.update', $entreprise->ID), [
            'titre' => 'Cabinet Kouassi & Associés',
            'type_id' => 1,
            'client' => 'OUI',
            'activation' => 1,
            'logo' => UploadedFile::fake()->image('nouveau.jpg', 300, 300),
        ])->assertSessionHas('success');

        $nouveau = $annuaire->fresh()->LOGO;
        $this->assertNotSame($ancien, $nouveau);
        $this->assertFileDoesNotExist(Media::path('logos', $ancien));
        $this->assertFileExists(Media::path('logos', $nouveau));
    }

    public function test_supprimer_une_entreprise_supprime_ses_fichiers(): void
    {
        $admin = $this->admin();
        $entreprise = Entreprise::create(['TITRE' => 'Boutique Test', 'LIEN' => 'boutique-test', 'TYPE_ID' => 1]);
        $annuaire = Annuaire::create(['ENTREPRISE_ID' => $entreprise->ID, 'LOGO' => $this->fauxFichier('logos', 'boutique.png')]);
        PhotoAnnuaire::create(['ANNUAIRE_ID' => $annuaire->ID, 'IMAGE' => $this->fauxFichier('galerie', 'vitrine.jpg')]);

        $this->actingAs($admin)->delete(route('admin.entreprises.destroy', $entreprise->ID))->assertRedirect(route('admin.entreprises.index'));

        $this->assertDatabaseMissing('entreprises', ['ID' => $entreprise->ID]);
        $this->assertFileDoesNotExist(Media::path('logos', 'boutique.png'));
        $this->assertFileDoesNotExist(Media::path('galerie', 'vitrine.jpg'));
    }

    public function test_galerie_et_rubriques(): void
    {
        $admin = $this->admin();
        $entreprise = Entreprise::create(['TITRE' => 'Hôtel Ivoire Test', 'LIEN' => 'hotel-test', 'TYPE_ID' => 1]);
        Annuaire::create(['ENTREPRISE_ID' => $entreprise->ID, 'CLIENT' => 'NON']);
        $sous = SousCategorie::create(['CATEGORIE_ID' => 18, 'DESIGNATION' => 'Hôtels', 'LIEN' => 'hotels', 'ACTIVATION' => 1]);

        $this->actingAs($admin)->post(route('admin.entreprises.photos.store', $entreprise->ID), [
            'photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertSessionHas('success');
        $this->assertSame(2, PhotoAnnuaire::count());

        $photo = PhotoAnnuaire::first();
        $this->actingAs($admin)->delete(route('admin.entreprises.photos.destroy', [$entreprise->ID, $photo->ID]));
        $this->assertFileDoesNotExist(Media::path('galerie', $photo->IMAGE));

        $this->actingAs($admin)->post(route('admin.entreprises.thematiques.store', $entreprise->ID), ['sous_categorie_id' => $sous->ID]);
        $this->actingAs($admin)->post(route('admin.entreprises.thematiques.store', $entreprise->ID), ['sous_categorie_id' => $sous->ID]);
        $this->assertDatabaseCount('thematique_annuaire', 1);
        $this->assertDatabaseHas('thematique_annuaire', ['ENTREPRISE_ID' => $entreprise->ID, 'CATEGORIE_ID' => 18, 'SOUS_CATEGORIE_ID' => $sous->ID]);
    }
}
