<?php

namespace Tests\Feature\Api;

use App\Models\CategorieAnnuaire;
use App\Models\CategorieImmobilier;
use App\Models\Entreprise;
use App\Models\News;
use App\Models\PapierAdministratif;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use App\Models\SourceNews;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use App\Models\VideoInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublicApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_communes(): void
    {
        $this->commune('Cocody', 9);
        $this->commune('Bouaké', 30);

        $this->getJson('/api/v1/communes')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.lien', 'cocody')
            ->assertJsonPath('data.1.abidjan', true)
            ->assertJsonPath('data.0.abidjan', false);
    }

    public function test_annuaire_categories_sous_categories_et_entreprises_clients_d_abord(): void
    {
        $cocody = $this->commune('Cocody', 9);
        $sante = CategorieAnnuaire::create(['DESIGNATION' => 'Santé', 'LIEN' => 'sante']);
        $pharmacies = SousCategorie::create(['CATEGORIE_ID' => $sante->ID, 'DESIGNATION' => 'Pharmacies', 'LIEN' => 'pharmacies', 'ACTIVATION' => 1]);

        $simple = $this->entreprise(['TITRE' => 'AAA Pharmacie simple', 'COMMUNE_ID' => $cocody->ID]);
        $client = $this->entreprise(['TITRE' => 'ZZZ Pharmacie cliente', 'COMMUNE_ID' => $cocody->ID], ['CLIENT' => 'OUI', 'VIP' => 1, 'LOGO' => 'logo.png']);
        $ailleurs = $this->entreprise(['TITRE' => 'Pharmacie de Bouaké']);
        foreach ([$simple, $client, $ailleurs] as $e) {
            ThematiqueAnnuaire::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => $sante->ID, 'SOUS_CATEGORIE_ID' => $pharmacies->ID]);
        }

        $this->getJson('/api/v1/annuaire/categories')->assertOk()->assertJsonPath('data.0.nombre_entreprises', 3);
        $this->getJson('/api/v1/annuaire/categories/sante/sous-categories')->assertOk()->assertJsonPath('data.0.lien', 'pharmacies');

        $this->getJson("/api/v1/annuaire/sous-categories/{$pharmacies->ID}/entreprises?commune=cocody")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.titre', 'ZZZ Pharmacie cliente')
            ->assertJsonPath('data.0.est_client', true)
            ->assertJsonPath('data.0.logo_url', url('/images/Logos/logo.png'))
            ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'total'], 'sous_categorie']);
    }

    public function test_commune_inconnue_renvoie_404(): void
    {
        $this->getJson('/api/v1/pharmacies/garde?commune=nulle-part')->assertNotFound();
    }

    public function test_fiche_entreprise_detaillee_et_compteur_de_vues(): void
    {
        $e = $this->entreprise(['TITRE' => 'Clinique du Plateau', 'LIEN' => 'clinique-du-plateau'], [
            'TEL_1' => '2720212223', 'EMAIL_1' => 'contact@clinique.ci', 'SITE' => 'clinique.ci', 'LATITUDE' => 5.32, 'LONGITUDE' => -4.02,
        ]);

        $this->getJson('/api/v1/annuaire/entreprises/clinique-du-plateau')
            ->assertOk()
            ->assertJsonPath('data.titre', 'Clinique du Plateau')
            ->assertJsonPath('data.telephones.0', '2720212223')
            ->assertJsonPath('data.emails.0', 'contact@clinique.ci')
            ->assertJsonPath('data.site', 'https://clinique.ci')
            ->assertJsonPath('data.latitude', 5.32);

        $this->assertSame(1.0, (float) Entreprise::find($e->ID)->COMPTEUR);
        $this->getJson('/api/v1/annuaire/entreprises/inexistante')->assertNotFound();
    }

    public function test_pharmacies_de_garde_par_commune(): void
    {
        $cocody = $this->commune('Cocody', 9);
        PeriodeGarde::create(['DEBUT' => now()->subDays(2)->toDateString(), 'FIN' => now()->addDays(4)->toDateString()]);
        $deGarde = $this->entreprise(['TITRE' => 'Pharmacie des Deux Plateaux', 'COMMUNE_ID' => $cocody->ID], ['TEL_1' => '2722000000']);
        $pasDeGarde = $this->entreprise(['TITRE' => 'Pharmacie fermée', 'COMMUNE_ID' => $cocody->ID]);
        $this->garde($deGarde);
        $this->garde($pasDeGarde, now()->subDays(20), now()->subDays(14));

        $this->getJson('/api/v1/pharmacies/garde')
            ->assertOk()
            ->assertJsonPath('data.communes.0.lien', 'cocody')
            ->assertJsonPath('data.communes.0.nombre_pharmacies', 1)
            ->assertJsonStructure(['data' => ['periode' => ['debut', 'fin', 'libelle']]]);

        $this->getJson('/api/v1/pharmacies/garde?commune=cocody')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titre', 'Pharmacie des Deux Plateaux')
            ->assertJsonPath('data.0.telephones.0', '2722000000')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_pharmacies_a_proximite_triees_par_distance(): void
    {
        // Point de référence : Plateau (5.3200, -4.0200)
        $loin = $this->entreprise(['TITRE' => 'Pharmacie à 2 km'], ['LATITUDE' => 5.3380, 'LONGITUDE' => -4.0200]);
        $proche = $this->entreprise(['TITRE' => 'Pharmacie à 500 m'], ['LATITUDE' => 5.3245, 'LONGITUDE' => -4.0200]);
        $horsRayon = $this->entreprise(['TITRE' => 'Pharmacie de Bassam'], ['LATITUDE' => 5.2000, 'LONGITUDE' => -3.7400]);
        $fermee = $this->entreprise(['TITRE' => 'Pharmacie pas de garde'], ['LATITUDE' => 5.3210, 'LONGITUDE' => -4.0200]);
        foreach ([$loin, $proche, $horsRayon] as $e) {
            $this->garde($e);
        }

        $reponse = $this->getJson('/api/v1/pharmacies/garde/proximite?lat=5.32&lng=-4.02&rayon=5')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.titre', 'Pharmacie à 500 m')
            ->assertJsonPath('data.1.titre', 'Pharmacie à 2 km');

        $this->assertEqualsWithDelta(0.5, $reponse->json('data.0.distance_km'), 0.05);
        $this->assertEqualsWithDelta(2.0, $reponse->json('data.1.distance_km'), 0.05);

        $this->getJson('/api/v1/pharmacies/garde/proximite?lat=abc&lng=-4')
            ->assertUnprocessable()
            ->assertJsonPath('errors.lat.0', 'Le champ latitude doit être un nombre.');
        $this->getJson('/api/v1/pharmacies/garde/proximite?lat=5&lng=-4&rayon=500')->assertUnprocessable();
    }

    public function test_titrologie_groupee_par_type_et_derniere_date_par_defaut(): void
    {
        $fratmat = SourceNews::create(['DESIGNATION' => 'Fraternité Matin', 'ACTIVATION' => 1]);
        $hier = now()->subDay()->toDateString();
        $this->une($fratmat, 1, $hier, 'fratmat.jpg');
        $this->une($fratmat, 2, $hier, 'hebdo.jpg');
        $this->une($fratmat, 1, now()->subDays(5)->toDateString(), 'ancienne.jpg');

        $this->getJson('/api/v1/news/titrologie')
            ->assertOk()
            ->assertJsonPath('data.date', $hier)
            ->assertJsonCount(1, 'data.quotidiens')
            ->assertJsonCount(1, 'data.hebdomadaires')
            ->assertJsonCount(0, 'data.mensuels')
            ->assertJsonPath('data.quotidiens.0.titre', 'Fraternité Matin')
            ->assertJsonPath('data.quotidiens.0.image_url', url('/images/ImagesNews/fratmat.jpg'));

        $this->getJson('/api/v1/news/titrologie?date='.now()->subDays(5)->toDateString())
            ->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/news/titrologie?date=25-11-2026')->assertUnprocessable();
    }

    public function test_news_web_et_videos_paginees(): void
    {
        $e = Entreprise::create(['TITRE' => 'Article du jour', 'LIEN' => 'article-du-jour', 'TYPE_ID' => 2]);
        News::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => 2, 'LIEN_WEB' => 'https://exemple.ci/article', 'RESUME' => '<p>Résumé</p>', 'DATE_PARUTION' => now()->toDateString()]);
        VideoInfo::create(['libelle' => 'Le journal', 'lien' => 'https://www.youtube.com/embed/jXYLopfT7ig?si=abc']);

        $this->getJson('/api/v1/news/web')
            ->assertOk()
            ->assertJsonPath('data.0.titre', 'Article du jour')
            ->assertJsonPath('data.0.resume', 'Résumé')
            ->assertJsonPath('data.0.lien_article', 'https://exemple.ci/article')
            ->assertJsonStructure(['links', 'meta']);

        $this->getJson('/api/v1/news/videos')
            ->assertOk()
            ->assertJsonPath('data.0.youtube_id', 'jXYLopfT7ig')
            ->assertJsonPath('data.0.vignette_url', 'https://i.ytimg.com/vi/jXYLopfT7ig/hqdefault.jpg');
    }

    public function test_papiers_administratifs_nationaux_et_locaux(): void
    {
        $cocody = $this->commune('Cocody', 9);
        $passeport = PapierAdministratif::create(['TITRE' => 'Passeport', 'ACTIVE' => 1, 'ORDRE' => 1]);
        $passeport->pieces()->create(['LIBELLE' => 'Extrait de naissance', 'RANG' => 1]);
        PapierAdministratif::create(['TITRE' => 'Mairie de Cocody', 'ACTIVE' => 1, 'COMMUNE_ID' => $cocody->ID]);
        PapierAdministratif::create(['TITRE' => 'Archivé', 'ACTIVE' => 0]);

        $this->getJson('/api/v1/papiers-administratifs?commune=cocody')
            ->assertOk()
            ->assertJsonCount(1, 'data.nationaux')
            ->assertJsonPath('data.nationaux.0.pieces.0', 'Extrait de naissance')
            ->assertJsonPath('data.locaux.0.titre', 'Mairie de Cocody')
            ->assertJsonPath('data.communes_disponibles.0.lien', 'cocody');
    }

    public function test_biens_immobiliers_filtres_et_seulement_valides(): void
    {
        $pro = $this->pro();
        $cocody = $this->commune('Cocody', 9);
        $appart = CategorieImmobilier::create(['DESIGNATION' => 'Appartement', 'ACTIVATION' => 1]);

        $this->bien($pro, ['TYPE_IMMOBILIER_ID' => 1, 'PRIX' => '300000', 'CATEGORIE_IMMOBILIER_ID' => $appart->ID, 'CHAMBRE' => 2], ['TITRE' => 'Appartement à louer', 'COMMUNE_ID' => $cocody->ID]);
        $this->bien($pro, ['TYPE_IMMOBILIER_ID' => 2, 'PRIX' => '90000000'], ['TITRE' => 'Villa chère']);
        $this->bien($pro, ['TYPE_IMMOBILIER_ID' => 3, 'PRIX' => '40000000'], ['TITRE' => 'Villa promoteur']);
        $this->bien($pro, ['VALIDATION' => 'NON'], ['TITRE' => 'En attente', 'LIEN' => 'en-attente']);

        $this->getJson('/api/v1/immobilier/biens')->assertOk()->assertJsonPath('meta.total', 3);
        $this->getJson('/api/v1/immobilier/biens?transaction=location')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.transaction', 'location')
            ->assertJsonPath('data.0.prix_affiche', "300\u{202F}000 FCFA / mois")
            ->assertJsonPath('data.0.commune.lien', 'cocody');
        $this->getJson('/api/v1/immobilier/biens?transaction=vente&tri=prix_asc')
            ->assertOk()
            ->assertJsonPath('data.0.titre', 'Villa promoteur')
            ->assertJsonPath('data.0.type_annonceur', 'promoteur');
        $this->getJson('/api/v1/immobilier/biens?prix_min=50000000')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/immobilier/biens?commune=cocody&chambres=2&categorie='.$appart->ID)->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/immobilier/categories')->assertOk()->assertJsonPath('data.0.nombre_biens', 1);
    }

    public function test_fiche_bien_annonceur_et_bien_non_valide_invisible(): void
    {
        $pro = $this->pro();
        $bien = $this->bien($pro, ['PISCINE' => 'OUI', 'DESCRIPTION' => 'Belle villa'], ['TITRE' => 'Villa avec piscine', 'LIEN' => 'villa-avec-piscine']);
        $bien->photos()->create(['IMAGE' => 'photo1.jpg']);
        $this->bien($pro, ['VALIDATION' => 'NON'], ['TITRE' => 'Brouillon', 'LIEN' => 'brouillon']);

        $this->getJson('/api/v1/immobilier/biens/villa-avec-piscine')
            ->assertOk()
            ->assertJsonPath('data.equipements.piscine', true)
            ->assertJsonPath('data.equipements_liste.0', 'Piscine')
            ->assertJsonPath('data.photos.0.url', url('/images/ImagesImmobiliers/photo1.jpg'))
            ->assertJsonPath('data.annonceur.entreprise_id', $pro->entreprise_id);

        $this->getJson('/api/v1/immobilier/biens/brouillon')->assertNotFound();
        $this->getJson('/api/v1/immobilier/biens/brouillon', $this->enTetes($pro))->assertOk();
        $this->assertDatabaseHas('statistiques', ['ENTREPRISE_ID' => $pro->entreprise_id, 'TYPE_STAT' => 3, 'STATS' => 1]);
    }

    public function test_annonceurs_par_type(): void
    {
        $agence = $this->pro();
        $promoteur = $this->pro();
        $this->bien($agence, ['TYPE_IMMOBILIER_ID' => 1]);
        $this->bien($agence, ['TYPE_IMMOBILIER_ID' => 2]);
        $this->bien($promoteur, ['TYPE_IMMOBILIER_ID' => 3]);

        $this->getJson('/api/v1/immobilier/annonceurs?type=agence')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $agence->id)
            ->assertJsonPath('data.0.nombre_biens', 2);
        $this->getJson('/api/v1/immobilier/annonceurs?type=promoteur')->assertOk()->assertJsonPath('data.0.id', $promoteur->id);
        $this->getJson('/api/v1/immobilier/annonceurs?type=banque')->assertUnprocessable();
    }

    public function test_statistiques_de_clic(): void
    {
        $e = $this->entreprise();

        $this->postJson("/api/v1/stats/{$e->ID}/appel")->assertNoContent();
        $this->postJson("/api/v1/stats/{$e->ID}/appel")->assertNoContent();
        $this->postJson("/api/v1/stats/{$e->ID}/email")->assertNoContent();
        $this->postJson('/api/v1/stats/999999/appel')->assertNotFound();
        $this->postJson("/api/v1/stats/{$e->ID}/sms")->assertNotFound();

        $this->assertDatabaseHas('statistiques', ['ENTREPRISE_ID' => $e->ID, 'TYPE_STAT' => 1, 'STATS' => 2]);
        $this->assertDatabaseHas('statistiques', ['ENTREPRISE_ID' => $e->ID, 'TYPE_STAT' => 2, 'STATS' => 1]);
    }

    private function garde(Entreprise $e, $debut = null, $fin = null): void
    {
        PharmacieGarde::create([
            'ENTREPRISE_ID' => $e->ID,
            'DEBUT_GARDE' => ($debut ?? now()->subDays(2))->toDateString(),
            'FIN_GARDE' => ($fin ?? now()->addDays(4))->toDateString(),
        ]);
    }

    private function une(SourceNews $source, int $type, string $date, string $image): void
    {
        $e = Entreprise::create(['TITRE' => $source->DESIGNATION.' '.$date, 'LIEN' => uniqid('une-'), 'TYPE_ID' => 2]);
        News::create(['ENTREPRISE_ID' => $e->ID, 'CATEGORIE_ID' => 3, 'TYPE_NEWS_ID' => $type, 'SOURCE_ID' => $source->ID, 'IMAGE' => $image, 'DATE_PARUTION' => $date, 'RANG' => 1]);
    }
}
