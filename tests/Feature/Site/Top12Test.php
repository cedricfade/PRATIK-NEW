<?php

namespace Tests\Feature\Site;

use App\Models\Annuaire;
use App\Models\CategorieAnnuaire;
use App\Models\Entreprise;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Les pros (clients / VIP) apparaissent dans le Top 12 de leur rubrique, avec une couronne. */
class Top12Test extends TestCase
{
    use RefreshDatabase;

    public function test_top12_et_couronne_dans_la_rubrique(): void
    {
        $cat = CategorieAnnuaire::create(['DESIGNATION' => 'Activités juridiques', 'LIEN' => 'activites-juridiques']);
        $sous = SousCategorie::create(['CATEGORIE_ID' => $cat->ID, 'DESIGNATION' => 'Notaires', 'LIEN' => 'notaires', 'ACTIVATION' => 1]);

        $fiche = function (string $titre, array $annuaire) use ($cat, $sous) {
            $e = Entreprise::create(['TITRE' => $titre, 'LIEN' => \Illuminate\Support\Str::slug($titre), 'TYPE_ID' => 1]);
            Annuaire::create(['ENTREPRISE_ID' => $e->ID] + $annuaire);
            ThematiqueAnnuaire::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => $cat->ID, 'SOUS_CATEGORIE_ID' => $sous->ID]);

            return $e;
        };

        $fiche('Etude Vip', ['CLIENT' => 'OUI', 'VIP' => 1]);
        $fiche('Etude Vip Deux', ['CLIENT' => 'NON', 'VIP' => 1]);
        $fiche('Etude Client Sans Vip', ['CLIENT' => 'OUI', 'VIP' => 0]);
        $fiche('Etude Standard', ['CLIENT' => 'NON', 'VIP' => 0]);

        $html = $this->get('/annuaire/activites-juridiques/notaires')->assertOk()
            ->assertSee('Top 12 · Notaires')
            ->assertSee('Toutes les entreprises')
            ->getContent();

        $this->assertSame(2, substr_count($html, 'TOP 12</span>'), 'Deux badges couronne attendus');
        $this->assertSame(1, substr_count($html, 'Etude Vip Deux'), 'Un pro du Top 12 ne doit pas être répété dans la liste');

        $this->get('/details/etude-vip')->assertOk()->assertSee('TOP 12');
        $this->get('/details/etude-client-sans-vip')->assertOk()->assertDontSee('TOP 12</span>', false);
        $this->get('/details/etude-standard')->assertOk()->assertDontSee('TOP 12</span>', false);
    }
}
