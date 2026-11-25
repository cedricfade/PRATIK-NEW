<?php

namespace Tests\Feature\Api;

use App\Models\ParametreSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccueilApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_accueil_renvoie_hero_chiffres_et_listes(): void
    {
        ParametreSite::enregistrer(['hero.accueil.titre' => 'Tout Abidjan,', 'hero.accueil.titre_accent' => 'en un clic.']);

        $this->getJson('/api/v1/accueil')
            ->assertOk()
            ->assertJsonPath('data.hero.titre', 'Tout Abidjan,')
            ->assertJsonPath('data.hero.titre_accent', 'en un clic.')
            ->assertJsonPath('data.hero.affichage', 'photo')
            ->assertJsonStructure(['data' => [
                'hero' => ['image_url', 'surtitre', 'voile', 'cadrage'],
                'hero_immobilier' => ['image_url', 'titre'],
                'chiffres' => ['entreprises', 'biens', 'pharmacies', 'visiteurs', 'afficher'],
                'partenaires', 'derniers_biens',
            ]]);
    }
}
