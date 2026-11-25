<?php

namespace Tests\Feature\Site;

use App\Models\Annuaire;
use App\Models\CategorieAnnuaire;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

abstract class SiteTestCase extends TestCase
{
    protected function commune(string $nom = 'Cocody', ?int $id = null): Commune
    {
        return Commune::forceCreate(array_filter(['ID' => $id, 'DESIGNATION' => $nom, 'LIEN' => \Illuminate\Support\Str::slug($nom)]));
    }

    /** Crée une entreprise de l'annuaire avec sa fiche. */
    protected function entreprise(array $entreprise = [], array $annuaire = []): Entreprise
    {
        $e = Entreprise::create($entreprise + [
            'TITRE' => 'Entreprise Test',
            'LIEN' => 'entreprise-test-'.uniqid(),
            'TYPE_ID' => 1,
            'ACTIVITE' => 'Conseil',
            'PRIORITE' => 0,
            'COMPTEUR' => 0,
        ]);

        Annuaire::create($annuaire + ['ENTREPRISE_ID' => $e->ID, 'CLIENT' => 'NON', 'TEL_1' => '27 22 00 00 00', 'EMAIL_1' => 'contact@test.ci']);

        return $e->fresh();
    }

    protected function rubrique(string $cat = 'Santé', string $sous = 'Pharmacies'): array
    {
        $c = CategorieAnnuaire::create(['DESIGNATION' => $cat, 'LIEN' => \Illuminate\Support\Str::slug($cat)]);
        $s = SousCategorie::create(['CATEGORIE_ID' => $c->ID, 'DESIGNATION' => $sous, 'LIEN' => \Illuminate\Support\Str::slug($sous), 'ACTIVATION' => 1]);

        return [$c, $s];
    }

    protected function classer(Entreprise $e, CategorieAnnuaire $c, SousCategorie $s): void
    {
        ThematiqueAnnuaire::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => $c->ID, 'SOUS_CATEGORIE_ID' => $s->ID]);
    }

    /** Champs anti-spam d'un humain ayant mis 10 secondes à remplir le formulaire. */
    protected function humain(array $donnees): array
    {
        return $donnees + ['_horodatage' => Crypt::encryptString((string) (time() - 10)), 'site_internet' => ''];
    }
}
