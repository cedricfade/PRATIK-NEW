<?php

namespace Tests\Feature\Admin;

use App\Models\Annuaire;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VuesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role_id' => 2, 'active' => 1]);
    }

    private function entreprise(string $titre, int $compteur = 10, array $annuaire = []): Entreprise
    {
        $e = Entreprise::create(['TITRE' => $titre, 'LIEN' => \Illuminate\Support\Str::slug($titre), 'TYPE_ID' => 1, 'COMPTEUR' => $compteur]);
        Annuaire::create(['ENTREPRISE_ID' => $e->ID] + $annuaire);

        return $e;
    }

    public function test_ajout_a_tout_l_annuaire_sans_toucher_aux_vues_reelles(): void
    {
        $a = $this->entreprise('Alpha', 10);
        $b = $this->entreprise('Beta', 3);

        $this->actingAs($this->admin)->post('/admin/vues/ajouter', ['perimetre' => 'annuaire_tous', 'minimum' => 50])->assertRedirect();

        $this->assertSame(10, (int) $a->fresh()->COMPTEUR);
        $this->assertSame(60, $a->fresh()->vues_affichees);
        $this->assertSame(53, $b->fresh()->vues_affichees);
        $this->assertDatabaseHas('historique_vues', ['perimetre' => 'annuaire_tous', 'operation' => 'ajout', 'fiches' => 2]);

        $this->get('/details/alpha')->assertOk()->assertSee('61 vues'); // 60 + la visite en cours
    }

    public function test_ajout_aleatoire_reserve_aux_vip(): void
    {
        $vip = $this->entreprise('Client VIP', 0, ['VIP' => 1]);
        $client = $this->entreprise('Client VIP Deux', 0, ['VIP' => 1, 'CLIENT' => 'OUI']);
        $simple = $this->entreprise('Client sans VIP', 0, ['VIP' => 0, 'CLIENT' => 'OUI']);

        $this->actingAs($this->admin)->post('/admin/vues/ajouter', ['perimetre' => 'annuaire_vip', 'minimum' => 100, 'maximum' => 200]);

        foreach ([$vip, $client] as $e) {
            $this->assertGreaterThanOrEqual(100, $e->fresh()->VUES_BONUS);
            $this->assertLessThanOrEqual(200, $e->fresh()->VUES_BONUS);
        }
        $this->assertSame(0, (int) $simple->fresh()->VUES_BONUS);
    }

    public function test_cas_par_cas_et_remise_a_zero(): void
    {
        $e = $this->entreprise('Unique', 7);

        $this->actingAs($this->admin)->post('/admin/vues/ajouter', ['perimetre' => 'fiche', 'fiche' => $e->ID, 'minimum' => 500]);
        $this->assertSame(507, $e->fresh()->vues_affichees);

        $this->actingAs($this->admin)->post('/admin/vues/remise-a-zero', ['perimetre' => 'fiche', 'fiche' => $e->ID]);
        $this->assertSame(7, $e->fresh()->vues_affichees);
    }

    public function test_ajout_sur_les_annonces_immobilieres_en_ligne(): void
    {
        $enLigne = Entreprise::create(['TITRE' => 'Villa', 'LIEN' => 'villa-1', 'TYPE_ID' => 3]);
        Immobilier::create(['ENTREPRISE_ID' => $enLigne->ID, 'VALIDATION' => 'OUI']);
        $enAttente = Entreprise::create(['TITRE' => 'Studio', 'LIEN' => 'studio-2', 'TYPE_ID' => 3]);
        Immobilier::create(['ENTREPRISE_ID' => $enAttente->ID, 'VALIDATION' => 'NON']);

        $this->actingAs($this->admin)->post('/admin/vues/ajouter', ['perimetre' => 'immo_biens', 'minimum' => 30]);

        $this->assertSame(30, (int) $enLigne->fresh()->VUES_BONUS);
        $this->assertSame(0, (int) $enAttente->fresh()->VUES_BONUS);
    }

    public function test_ecran_et_recherche(): void
    {
        $e = $this->entreprise('Pharmacie du Plateau', 12);

        $this->actingAs($this->admin)->get('/admin/vues')->assertOk()->assertSee("Augmenter les vues d'un groupe", false);
        // Recherche par numéro de fiche (l'index plein texte ne voit pas les lignes non validées des tests).
        $this->actingAs($this->admin)->get('/admin/vues?q='.$e->ID)->assertOk()->assertSee('Pharmacie du Plateau');
    }

    public function test_reserve_aux_administrateurs(): void
    {
        $pro = User::factory()->create(['role_id' => 5, 'active' => 1]);

        $this->actingAs($pro)->post('/admin/vues/ajouter', ['perimetre' => 'annuaire_tous', 'minimum' => 999])->assertRedirect();
        $this->assertSame(0, DB::table('historique_vues')->count());
    }
}
