<?php

namespace Tests\Feature\Admin;

use App\Models\Annuaire;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected function admin(int $role = 1): User
    {
        return User::factory()->create(['role_id' => $role, 'active' => 1]);
    }

    protected function pharmacie(string $titre, Commune $commune): Entreprise
    {
        $sous = SousCategorie::find(config('pratik.pharmacie_sous_categorie'))
            ?? SousCategorie::forceCreate(['ID' => config('pratik.pharmacie_sous_categorie'), 'CATEGORIE_ID' => 16, 'DESIGNATION' => 'Pharmacies', 'LIEN' => 'pharmacies', 'ACTIVATION' => 1]);
        $e = Entreprise::create(['TITRE' => $titre, 'LIEN' => \Illuminate\Support\Str::slug($titre), 'TYPE_ID' => 1, 'COMMUNE_ID' => $commune->ID]);
        Annuaire::create(['ENTREPRISE_ID' => $e->ID, 'CLIENT' => 'NON', 'TEL_1' => '27 22 00 00 00']);
        ThematiqueAnnuaire::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => $sous->CATEGORIE_ID, 'SOUS_CATEGORIE_ID' => $sous->ID]);

        return $e;
    }
}
