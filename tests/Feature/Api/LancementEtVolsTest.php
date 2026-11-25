<?php

namespace Tests\Feature\Api;

use App\Models\ParametreSite;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LancementEtVolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pas_de_pub_par_defaut(): void
    {
        $this->getJson('/api/v1/lancement')->assertOk()->assertJsonPath('data.pub', null);
    }

    public function test_pub_configuree_dans_l_admin_puis_renvoyee_a_l_app(): void
    {
        $admin = User::factory()->create(['role_id' => 2, 'active' => 1]);

        $this->actingAs($admin)->get('/admin/apparence/application')->assertOk()->assertSee('Publicité à l');

        $this->actingAs($admin)->put('/admin/apparence/application', [
            'actif' => '1', 'duree' => 6, 'lien' => 'https://pratik-ci.com/immobilier',
            'image' => UploadedFile::fake()->image('pub.jpg', 1080, 1920),
        ])->assertRedirect('/admin/apparence/application');

        $fichier = ParametreSite::valeur('app.pub.image');
        $this->assertFileExists(Media::path('heros', $fichier));

        $this->getJson('/api/v1/lancement')->assertOk()
            ->assertJsonPath('data.pub.duree', 6)
            ->assertJsonPath('data.pub.lien', 'https://pratik-ci.com/immobilier')
            ->assertJsonPath('data.pub.image_url', fn ($url) => str_ends_with($url, 'Heros/'.$fichier));

        // Retirer l'image supprime le fichier et coupe la publicité.
        $this->actingAs($admin)->put('/admin/apparence/application', ['actif' => '1', 'duree' => 5, 'supprimer_image' => '1']);
        $this->assertFileDoesNotExist(Media::path('heros', $fichier));
        $this->getJson('/api/v1/lancement')->assertJsonPath('data.pub', null);
    }

    public function test_pub_hors_periode_non_affichee(): void
    {
        ParametreSite::enregistrer(['app.pub.actif' => '1', 'app.pub.image' => 'x.jpg', 'app.pub.fin' => now()->subDay()->toDateString()]);

        $this->getJson('/api/v1/lancement')->assertJsonPath('data.pub', null);
    }

    public function test_activer_sans_image_est_refuse(): void
    {
        $admin = User::factory()->create(['role_id' => 2, 'active' => 1]);

        $this->actingAs($admin)->put('/admin/apparence/application', ['actif' => '1', 'duree' => 5])->assertSessionHasErrors('image');
    }

    public function test_vols_renvoie_departs_et_arrivees(): void
    {
        Cache::put('vols:ABJ:'.now()->toDateString(), [
            'departures' => [[
                'number' => 'AF 703', 'status' => 'Expected', 'airline' => ['name' => 'Air France', 'iata' => 'AF'],
                'departure' => ['scheduledTime' => ['local' => now()->toDateString().' 23:15+00:00'], 'terminal' => '1', 'gate' => '4'],
                'arrival' => ['airport' => ['municipalityName' => 'Paris', 'iata' => 'CDG']],
            ]],
            'arrivals' => [],
            'erreur' => false,
        ], 300);

        $this->getJson('/api/v1/vols')->assertOk()
            ->assertJsonPath('data.aeroport', 'ABJ')
            ->assertJsonPath('data.departs.0.numero', 'AF 703')
            ->assertJsonPath('data.departs.0.ville', 'Paris')
            ->assertJsonPath('data.departs.0.heure', '23:15')
            ->assertJsonPath('data.indisponible', false)
            ->assertJsonStructure(['data' => ['aeroports' => [['code', 'nom']], 'arrivees']]);
    }
}
