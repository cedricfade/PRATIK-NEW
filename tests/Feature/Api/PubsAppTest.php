<?php

namespace Tests\Feature\Api;

use App\Models\PubApp;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PubsAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_bannieres_gerees_dans_l_admin_et_renvoyees_a_l_app(): void
    {
        $admin = User::factory()->create(['role_id' => 2, 'active' => 1]);

        $this->actingAs($admin)->post('/admin/apparence/application/pubs', [
            'image' => UploadedFile::fake()->image('b.jpg', 1200, 600), 'titre' => 'Promo', 'lien' => 'pratik://app/immobilier', 'ordre' => 1, 'actif' => '1',
        ])->assertRedirect();
        $this->actingAs($admin)->post('/admin/apparence/application/pubs', [
            'image' => UploadedFile::fake()->image('c.jpg', 1200, 600), 'lien' => 'https://pratik-ci.com', 'ordre' => 0, 'actif' => '0',
        ])->assertRedirect();

        $this->getJson('/api/v1/accueil')->assertOk()
            ->assertJsonCount(1, 'data.pubs')
            ->assertJsonPath('data.pubs.0.lien', 'pratik://app/immobilier');

        $pub = PubApp::where('titre', 'Promo')->firstOrFail();
        $fichier = $pub->image;
        $this->assertFileExists(Media::path('heros', $fichier));

        $this->actingAs($admin)->delete('/admin/apparence/application/pubs/'.$pub->id)->assertRedirect();
        $this->assertFileDoesNotExist(Media::path('heros', $fichier));
        $this->getJson('/api/v1/accueil')->assertJsonCount(0, 'data.pubs');
    }

    public function test_lien_invalide_refuse(): void
    {
        $admin = User::factory()->create(['role_id' => 2, 'active' => 1]);

        $this->actingAs($admin)->post('/admin/apparence/application/pubs', [
            'image' => UploadedFile::fake()->image('b.jpg', 1200, 600), 'lien' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('lien');
    }
}
