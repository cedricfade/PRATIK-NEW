<?php

namespace Tests\Feature\Site;

use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PharmacieTest extends SiteTestCase
{
    use RefreshDatabase;

    private function deGarde(string $titre, int $communeId, float $lat, float $lng): void
    {
        $e = $this->entreprise(['TITRE' => $titre, 'COMMUNE_ID' => $communeId], ['LATITUDE' => $lat, 'LONGITUDE' => $lng, 'ADRESSE' => 'Rue '.$titre]);
        PharmacieGarde::create(['ENTREPRISE_ID' => $e->ID, 'DEBUT_GARDE' => now()->subDays(2)->toDateString(), 'FIN_GARDE' => now()->addDays(4)->toDateString()]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        PeriodeGarde::create(['DEBUT' => now()->subDays(2)->toDateString(), 'FIN' => now()->addDays(4)->toDateString()]);
        $cocody = $this->commune('Cocody', 9);
        $bouake = $this->commune('Bouaké', 40);
        $this->deGarde('Pharmacie Riviera Golf', $cocody->ID, 5.3400, -3.9700);
        $this->deGarde('Pharmacie Angré', $cocody->ID, 5.3950, -3.9900);
        $this->deGarde('Pharmacie du Centre Bouaké', $bouake->ID, 7.6900, -5.0300);

        // Une garde terminée ne doit jamais apparaître.
        $ancienne = $this->entreprise(['TITRE' => 'Pharmacie Ancienne Garde', 'COMMUNE_ID' => $cocody->ID]);
        PharmacieGarde::create(['ENTREPRISE_ID' => $ancienne->ID, 'DEBUT_GARDE' => now()->subDays(20)->toDateString(), 'FIN_GARDE' => now()->subDays(14)->toDateString()]);
    }

    public function test_la_page_liste_abidjan_puis_l_interieur(): void
    {
        $this->get('/pharmacies-de-garde')
            ->assertOk()
            ->assertSeeInOrder(['Abidjan', 'Cocody', 'Intérieur du pays', 'Bouaké']);
    }

    public function test_la_page_commune_liste_les_gardes_en_cours_uniquement(): void
    {
        $this->get('/pharmacies-de-garde/cocody')
            ->assertOk()
            ->assertSee('Pharmacie Riviera Golf')
            ->assertSee('Pharmacie Angré')
            ->assertDontSee('Pharmacie Ancienne Garde')
            ->assertDontSee('Pharmacie du Centre Bouaké');
    }

    public function test_autour_de_moi_trie_par_distance_et_ignore_les_pharmacies_lointaines(): void
    {
        // Position proche de la Riviera Golf.
        $this->post('/pharmacies-de-garde/autour-de-moi', ['latitude' => 5.3420, 'longitude' => -3.9720])
            ->assertOk()
            ->assertSee('Pharmacie Riviera Golf')
            ->assertDontSee('Pharmacie du Centre Bouaké');

        // Rien à moins de 3 km : le rayon passe à 10 km (Riviera ≈ 5,5 km, Angré ≈ 7 km).
        $this->get('/pharmacies-de-garde/autour-de-moi?latitude=5.3700&longitude=-3.9300')
            ->assertOk()
            ->assertSeeInOrder(['Pharmacie Riviera Golf', 'Pharmacie Angré']);
    }

    public function test_l_ancienne_route_de_proximite_reste_compatible(): void
    {
        $this->post('/pharmacie-proximite', ['latitude' => 7.69, 'longitude' => -5.03])
            ->assertOk()
            ->assertSee('Pharmacie du Centre Bouaké');
    }

    public function test_sans_position_on_revient_avec_une_erreur(): void
    {
        $this->from('/pharmacies-de-garde')->post('/pharmacies-de-garde/autour-de-moi', [])
            ->assertRedirect('/pharmacies-de-garde')
            ->assertSessionHasErrors('latitude');
    }
}
