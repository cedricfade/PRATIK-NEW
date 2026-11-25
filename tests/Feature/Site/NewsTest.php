<?php

namespace Tests\Feature\Site;

use App\Models\Entreprise;
use App\Models\News;
use App\Models\SourceNews;
use App\Models\SourceNewsWeb;
use App\Models\VideoInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsTest extends SiteTestCase
{
    use RefreshDatabase;

    private function une(string $journal, string $date, int $type = 1): News
    {
        $source = SourceNews::create(['DESIGNATION' => $journal, 'ACTIVATION' => 1]);
        $e = Entreprise::create(['TITRE' => $journal.' '.$date, 'LIEN' => \Illuminate\Support\Str::slug($journal.' '.$date), 'TYPE_ID' => 2]);

        return News::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => 3, 'TYPE_NEWS_ID' => $type, 'SOURCE_ID' => $source->ID, 'IMAGE' => 'une.jpg', 'DATE_PARUTION' => $date, 'RANG' => 1]);
    }

    public function test_les_unes_du_jour_sont_groupees_par_periodicite(): void
    {
        $this->une('Fraternité Matin', now()->toDateString(), 1);
        $this->une('Le Mensuel Éco', now()->toDateString(), 3);

        $this->get('/news')->assertOk()->assertSeeInOrder(['Quotidiens', 'Fraternité Matin', 'Mensuels', 'Le Mensuel Éco']);
    }

    public function test_sans_unes_aujourd_hui_on_affiche_la_derniere_parution(): void
    {
        $this->une('Le Patriote', now()->subDay()->toDateString());

        $this->get('/news')->assertOk()->assertSee('Le Patriote')->assertSee('dernière parution');
    }

    public function test_archives_et_detail_d_une_une(): void
    {
        $date = now()->subDays(5)->toDateString();
        $une = $this->une('Notre Voie', $date);
        $this->une('Le Temps', $date);

        $this->get('/archives-news?date_archive='.$date)->assertOk()->assertSee('Notre Voie')->assertSee('Le Temps');
        $this->get('/news/'.$une->entreprise()->value('LIEN'))->assertOk()->assertSee('Notre Voie')->assertSee('Les autres unes du jour');
        $this->get('/news?date=pas-une-date')->assertRedirect('/news');
    }

    public function test_article_web_avec_lien_historique_termine_par_un_slash(): void
    {
        $source = SourceNewsWeb::create(['DESIGNATION' => 'Le Monde', 'UTILISATEUR' => 1]);
        $e = Entreprise::create(['TITRE' => 'Un article important', 'LIEN' => '25112026-un-article-important/', 'TYPE_ID' => 2]);
        News::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => 2, 'SOURCE_WEB_ID' => $source->ID, 'LIEN_WEB' => 'https://www.lemonde.fr/x', 'RESUME' => 'Résumé de l\'article', 'DATE_PARUTION' => now()->toDateString()]);

        $this->get('/news-web')->assertOk()->assertSee('Un article important')->assertSee('/news-web/25112026-un-article-important"', false);
        $this->get('/news-web/25112026-un-article-important')->assertOk()->assertSee('Résumé de l&#039;article', false)->assertSee('https://www.lemonde.fr/x');
    }

    public function test_page_videos(): void
    {
        VideoInfo::create(['libelle' => 'Reportage cacao', 'lien' => 'https://www.youtube.com/embed/jXYLopfT7ig?si=abc']);

        $this->get('/news-videos')->assertOk()->assertSee('Reportage cacao')->assertSee('youtube-nocookie.com/embed/jXYLopfT7ig', false);
    }
}
