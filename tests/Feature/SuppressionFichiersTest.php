<?php

namespace Tests\Feature;

use App\Models\Annuaire;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\PhotoAnnuaire;
use App\Models\PhotoImmobilier;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Supprimer une fiche doit supprimer ses images du disque (demande client). */
class SuppressionFichiersTest extends TestCase
{
    use RefreshDatabase;

    public function test_supprimer_une_entreprise_supprime_logo_et_galerie(): void
    {
        $entreprise = Entreprise::create(['TITRE' => 'Test SARL', 'LIEN' => 'test-sarl', 'TYPE_ID' => 1]);
        $annuaire = Annuaire::create(['ENTREPRISE_ID' => $entreprise->ID, 'LOGO' => $this->fauxFichier('logos', 'logo-test.png')]);
        PhotoAnnuaire::create(['ANNUAIRE_ID' => $annuaire->ID, 'IMAGE' => $this->fauxFichier('galerie', 'galerie-1.jpg')]);

        $entreprise->delete();

        $this->assertFileDoesNotExist(Media::path('logos', 'logo-test.png'));
        $this->assertFileDoesNotExist(Media::path('galerie', 'galerie-1.jpg'));
        $this->assertDatabaseMissing('annuaires', ['ID' => $annuaire->ID]);
        $this->assertDatabaseCount('photo_annuaire', 0);
    }

    public function test_un_logo_partage_par_une_autre_fiche_est_conserve(): void
    {
        $fichier = $this->fauxFichier('logos', 'logo-groupe.png');
        $mere = Entreprise::create(['TITRE' => 'Groupe', 'LIEN' => 'groupe', 'TYPE_ID' => 1]);
        $filiale = Entreprise::create(['TITRE' => 'Filiale', 'LIEN' => 'filiale', 'TYPE_ID' => 1]);
        Annuaire::create(['ENTREPRISE_ID' => $mere->ID, 'LOGO' => $fichier]);
        Annuaire::create(['ENTREPRISE_ID' => $filiale->ID, 'LOGO' => $fichier]);

        $filiale->delete();

        $this->assertFileExists(Media::path('logos', $fichier));
    }

    public function test_remplacer_un_logo_supprime_l_ancien(): void
    {
        $entreprise = Entreprise::create(['TITRE' => 'Test', 'LIEN' => 'test', 'TYPE_ID' => 1]);
        $annuaire = Annuaire::create(['ENTREPRISE_ID' => $entreprise->ID, 'LOGO' => $this->fauxFichier('logos', 'ancien.png')]);

        $annuaire->update(['LOGO' => $this->fauxFichier('logos', 'nouveau.png')]);

        $this->assertFileDoesNotExist(Media::path('logos', 'ancien.png'));
        $this->assertFileExists(Media::path('logos', 'nouveau.png'));
    }

    public function test_supprimer_un_bien_supprime_sa_fiche_et_toutes_ses_photos(): void
    {
        $entreprise = Entreprise::create(['TITRE' => 'Villa', 'LIEN' => 'villa-1', 'TYPE_ID' => 3]);
        $bien = Immobilier::create(['ENTREPRISE_ID' => $entreprise->ID, 'ILLUSTATION' => $this->fauxFichier('immobilier', 'ill.jpg')]);
        PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'p1.jpg')]);
        PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'p2.jpg')]);

        $bien->supprimer();

        foreach (['ill.jpg', 'p1.jpg', 'p2.jpg'] as $f) {
            $this->assertFileDoesNotExist(Media::path('immobilier', $f));
        }
        $this->assertDatabaseMissing('entreprises', ['ID' => $entreprise->ID]);
        $this->assertDatabaseCount('photo_immobilier', 0);
    }

    public function test_l_image_par_defaut_n_est_jamais_supprimee(): void
    {
        $this->fauxFichier('immobilier', Immobilier::IMAGE_DEFAUT);
        $entreprise = Entreprise::create(['TITRE' => 'Terrain', 'LIEN' => 'terrain', 'TYPE_ID' => 3]);
        $bien = Immobilier::create(['ENTREPRISE_ID' => $entreprise->ID, 'ILLUSTATION' => Immobilier::IMAGE_DEFAUT]);

        $bien->supprimer();

        $this->assertFileExists(Media::path('immobilier', Immobilier::IMAGE_DEFAUT));
    }

    public function test_supprimer_une_photo_de_galerie_supprime_le_fichier(): void
    {
        $entreprise = Entreprise::create(['TITRE' => 'Appart', 'LIEN' => 'appart', 'TYPE_ID' => 3]);
        $bien = Immobilier::create(['ENTREPRISE_ID' => $entreprise->ID]);
        $photo = PhotoImmobilier::create(['IMMOBILIER_ID' => $bien->ID, 'IMAGE' => $this->fauxFichier('immobilier', 'g.jpg')]);

        $photo->delete();

        $this->assertFileDoesNotExist(Media::path('immobilier', 'g.jpg'));
    }
}
