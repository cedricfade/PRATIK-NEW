<?php

namespace Tests\Feature\Admin;

use App\Models\ParametreSite;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApparenceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role_id' => 2, 'active' => 1]);
    }

    private function donnees(array $extra = []): array
    {
        return $extra + [
            'affichage' => 'photo',
            'voile' => 50,
            'cadrage' => 'center',
            'surtitre' => 'Côte d\'Ivoire · {entreprises} entreprises',
            'titre' => 'Tout Abidjan,',
            'titre_accent' => 'en un clic.',
            'texte' => '',
            'chiffres_afficher' => '1',
            'chiffres_visiteurs' => '+12 000',
            'chiffres_visiteurs_libelle' => 'visiteurs par jour',
        ];
    }

    public function test_l_ecran_apparence_s_affiche_pour_les_deux_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/apparence')->assertOk()->assertSee('Aperçu');
        $this->actingAs($admin)->get('/admin/apparence?page=immobilier')->assertOk();
    }

    public function test_un_visiteur_ne_peut_pas_modifier_le_hero(): void
    {
        $membre = User::factory()->create(['role_id' => 7, 'active' => 1]);

        $this->actingAs($membre)->put('/admin/apparence/accueil', $this->donnees())->assertRedirect();
        $this->assertNull(ParametreSite::valeur('hero.accueil.titre'));
    }

    public function test_les_textes_et_l_image_apparaissent_sur_l_accueil(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/apparence/accueil', $this->donnees(['image' => UploadedFile::fake()->image('fond.jpg', 1920, 720)]))
            ->assertRedirect('/admin/apparence?page=accueil');

        $fichier = ParametreSite::valeur('hero.accueil.image');
        $this->assertNotNull($fichier);
        $this->assertFileExists(Media::path('heros', $fichier));

        $this->get('/')
            ->assertOk()
            ->assertSee('Tout Abidjan,')
            ->assertSee('en un clic.')
            ->assertSee('+12 000')
            ->assertSee('Heros/'.$fichier, false);
    }

    public function test_remplacer_l_image_supprime_l_ancienne_et_le_retour_par_defaut_aussi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/apparence/immobilier', $this->donnees(['image' => UploadedFile::fake()->image('a.jpg', 1600, 600)]));
        $premier = ParametreSite::valeur('hero.immobilier.image');

        $this->actingAs($admin)->put('/admin/apparence/immobilier', $this->donnees(['image' => UploadedFile::fake()->image('b.jpg', 1600, 600)]));
        $second = ParametreSite::valeur('hero.immobilier.image');

        $this->assertNotSame($premier, $second);
        $this->assertFileDoesNotExist(Media::path('heros', $premier));
        $this->assertFileExists(Media::path('heros', $second));

        $this->actingAs($admin)->put('/admin/apparence/immobilier', $this->donnees(['image_defaut' => '1']));

        $this->assertNull(ParametreSite::valeur('hero.immobilier.image'));
        $this->assertFileDoesNotExist(Media::path('heros', $second));
        $this->get('/immobilier')->assertOk()->assertSee('brand/hero-immobilier.jpg', false);
    }

    public function test_mode_banniere_affiche_l_image_sans_titre_visible(): void
    {
        $this->actingAs($this->admin())->put('/admin/apparence/accueil', $this->donnees(['affichage' => 'banniere']));

        $this->get('/')->assertOk()->assertSee('<h1 class="sr-only">', false);
    }

    public function test_les_chiffres_peuvent_etre_masques(): void
    {
        $this->actingAs($this->admin())->put('/admin/apparence/accueil', $this->donnees(['chiffres_afficher' => '0']));

        $this->get('/')->assertOk()->assertDontSee('+12 000');
    }
}
