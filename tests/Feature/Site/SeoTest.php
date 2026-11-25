<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\RefreshDatabase;

class SeoTest extends SiteTestCase
{
    use RefreshDatabase;

    public function test_le_sitemap_index_reference_les_sous_sitemaps(): void
    {
        $this->entreprise(['LIEN' => 'cabinet-sitemap']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/sitemaps/annuaire-0.xml', false)
            ->assertSee('/sitemaps/immobilier.xml', false);

        $this->get('/sitemaps/annuaire-0.xml')->assertOk()->assertSee('/details/cabinet-sitemap', false);
        $this->get('/sitemaps/annuaire-5.xml')->assertNotFound();

        foreach (['pages', 'annuaire-rubriques', 'immobilier', 'news'] as $fichier) {
            $this->get("/sitemaps/{$fichier}.xml")->assertOk();
        }
    }

    public function test_robots_txt_bloque_tout_hors_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_les_anciennes_urls_sont_redirigees_en_301(): void
    {
        $redirections = [
            '/pharmacieGarde' => '/pharmacies-de-garde',
            '/annuaires/cabinet-x' => '/details/cabinet-x',
            '/annuaire-recherches-notaire' => '/annuaire-recherche?q=notaire',
            '/new/souscription' => '/se-faire-connaitre',
            '/Horaires-Avions' => '/horaires-des-vols',
            '/lesbonsplans' => '/',
            '/bonsplans/Restaurant/maquis' => '/',
            '/news/sitenews.xml' => '/sitemap.xml',
            '/annuaire/siteannuaire.xml' => '/sitemap.xml',
            '/Pratik-Admin-Communes' => '/admin',
            '/home' => '/',
        ];

        foreach ($redirections as $ancienne => $nouvelle) {
            $this->get($ancienne)->assertStatus(301)->assertRedirect(url($nouvelle));
        }
    }

    public function test_page_404_dans_le_gabarit_du_site(): void
    {
        $this->get('/une-page-qui-n-existe-pas')->assertNotFound()->assertSee('Cette page n\'existe pas', false)->assertSee('Pharmacies de garde');
    }
}
