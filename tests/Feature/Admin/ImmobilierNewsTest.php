<?php

namespace Tests\Feature\Admin;

use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\News;
use App\Models\SourceNews;
use App\Models\TypeNews;
use App\Models\User;
use App\Support\Media;
use Illuminate\Http\UploadedFile;

class ImmobilierNewsTest extends AdminTestCase
{
    public function test_valider_puis_retirer_un_bien(): void
    {
        $admin = $this->admin(2);
        $e = Entreprise::create(['TITRE' => 'Villa Riviera', 'LIEN' => 'villa-riviera-1', 'TYPE_ID' => 3]);
        $bien = Immobilier::create(['ENTREPRISE_ID' => $e->ID, 'VALIDATION' => 'NON', 'TYPE_IMMOBILIER_ID' => 2]);

        $this->actingAs($admin)->patch(route('admin.biens.validation', $bien->ID))->assertSessionHas('success');
        $this->assertSame('OUI', $bien->fresh()->VALIDATION);

        $this->actingAs($admin)->patch(route('admin.biens.validation', $bien->ID));
        $this->assertSame('NON', $bien->fresh()->VALIDATION);

        $this->actingAs($admin)->patch(route('admin.biens.drapeau', [$bien->ID, 'LUXE']));
        $this->assertSame('OUI', $bien->fresh()->LUXE);
    }

    public function test_supprimer_un_bien_supprime_sa_fiche_et_ses_photos(): void
    {
        $admin = $this->admin();
        $e = Entreprise::create(['TITRE' => 'Duplex Cocody', 'LIEN' => 'duplex-1', 'TYPE_ID' => 3]);
        $bien = Immobilier::create(['ENTREPRISE_ID' => $e->ID, 'ILLUSTATION' => $this->fauxFichier('immobilier', 'duplex.jpg')]);

        $this->actingAs($admin)->delete(route('admin.biens.destroy', $bien->ID))->assertRedirect();

        $this->assertDatabaseMissing('immobilier', ['ID' => $bien->ID]);
        $this->assertDatabaseMissing('entreprises', ['ID' => $e->ID]);
        $this->assertFileDoesNotExist(Media::path('immobilier', 'duplex.jpg'));
    }

    public function test_publier_une_une_de_journal(): void
    {
        $admin = $this->admin();
        $source = SourceNews::create(['DESIGNATION' => 'Fraternité Matin', 'SLUG' => 'fraternite-matin', 'ACTIVATION' => 1]);
        TypeNews::forceCreate(['ID' => 1, 'DESIGNATION' => 'Quotidien', 'ACTIVATION' => 1]);

        $this->actingAs($admin)->post(route('admin.titrologie.store'), [
            'date_parution' => '2026-11-25',
            'unes' => [1 => ['source_id' => $source->ID, 'type_news_id' => 1, 'rang' => 1, 'image' => UploadedFile::fake()->image('une.jpg', 600, 800)]],
        ])->assertRedirect(route('admin.titrologie.index', ['date' => '2026-11-25']));

        $news = News::firstOrFail();
        $this->assertSame(3, (int) $news->CATEGORIE_ID);
        $this->assertSame('2026-11-25', $news->DATE_PARUTION);
        $this->assertFileExists(Media::path('news', $news->IMAGE));
        $this->assertDatabaseHas('entreprises', ['ID' => $news->ENTREPRISE_ID, 'TYPE_ID' => 2, 'TITRE' => 'Fraternité Matin 2026-11-25', 'LIEN' => 'fraternite-matin-2026-11-25']);

        // Seconde une du même journal le même jour : lien unique.
        $this->actingAs($admin)->post(route('admin.titrologie.store'), [
            'date_parution' => '2026-11-25',
            'unes' => [1 => ['source_id' => $source->ID, 'type_news_id' => 1, 'image' => UploadedFile::fake()->image('une2.jpg')]],
        ]);
        $this->assertDatabaseHas('entreprises', ['LIEN' => 'fraternite-matin-2026-11-25-2']);

        $this->actingAs($admin)->delete(route('admin.titrologie.destroy', $news->ID));
        $this->assertFileDoesNotExist(Media::path('news', $news->IMAGE));
        $this->assertDatabaseMissing('entreprises', ['ID' => $news->ENTREPRISE_ID]);
    }

    public function test_creer_un_annonceur_avec_une_nouvelle_fiche(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.annonceurs.store'), [
            'name' => 'Awa Koné',
            'email' => 'awa@agence-test.ci',
            'password' => 'motdepasse123',
            'categorie' => 'Immobilier',
            'nouvelle_entreprise' => 'Agence Awa Immo',
        ])->assertSessionHas('success');

        $user = User::where('email', 'awa@agence-test.ci')->firstOrFail();
        $this->assertSame(5, $user->role_id);
        $this->assertSame(1, $user->active);
        $this->assertSame('Agence Awa Immo', $user->entreprise->TITRE);
        $this->assertDatabaseHas('annuaires', ['ENTREPRISE_ID' => $user->entreprise_id, 'CLIENT' => 'OUI']);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('motdepasse123', $user->password));
    }

    public function test_creer_un_annonceur_rattache_a_une_entreprise_existante(): void
    {
        $existante = Entreprise::create(['TITRE' => 'SCI Test', 'LIEN' => 'sci-test', 'TYPE_ID' => 1]);

        $this->actingAs($this->admin())->post(route('admin.annonceurs.store'), [
            'name' => 'Responsable SCI', 'email' => 'sci@test.ci', 'password' => 'motdepasse123',
            'categorie' => 'Immobilier', 'entreprise_id' => $existante->ID,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'sci@test.ci', 'entreprise_id' => $existante->ID, 'role_id' => 5]);
        $this->assertSame(1, Entreprise::count());
    }

    public function test_seul_un_super_admin_cree_un_administrateur(): void
    {
        $this->actingAs($this->admin(2))->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'x@pratik.ci', 'password' => 'motdepasse-long', 'role_id' => 2,
        ])->assertForbidden();

        $this->actingAs($this->admin(1))->post(route('admin.users.store'), [
            'name' => 'Nouvel admin', 'email' => 'admin2@pratik.ci', 'password' => 'motdepasse-long', 'role_id' => 2,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'admin2@pratik.ci', 'role_id' => 2]);
    }
}
