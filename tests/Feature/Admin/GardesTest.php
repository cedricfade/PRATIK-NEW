<?php

namespace Tests\Feature\Admin;

use App\Models\Commune;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;

class GardesTest extends AdminTestCase
{
    public function test_creer_une_periode_puis_affecter_des_pharmacies_sans_doublon(): void
    {
        $admin = $this->admin();
        $cocody = Commune::create(['DESIGNATION' => 'Cocody', 'LIEN' => 'cocody']);
        $a = $this->pharmacie('Pharmacie des Lagunes', $cocody);
        $b = $this->pharmacie('Pharmacie Saint-Jean', $cocody);

        $this->actingAs($admin)->post(route('admin.periodes.store'), ['debut' => '2026-11-28', 'fin' => '2026-12-04'])
            ->assertRedirect();
        $periode = PeriodeGarde::firstOrFail();
        $this->assertSame('2026-12-04', $periode->FIN);

        $this->actingAs($admin)->post(route('admin.gardes.store'), ['periode_id' => $periode->ID, 'entreprises' => [$a->ID, $b->ID]])
            ->assertSessionHas('success');
        // Deuxième envoi avec une pharmacie déjà de garde : pas de doublon.
        $this->actingAs($admin)->post(route('admin.gardes.store'), ['periode_id' => $periode->ID, 'entreprises' => [$a->ID]]);

        $this->assertSame(2, PharmacieGarde::count());
        $this->assertDatabaseHas('pharmacie_garde', [
            'ENTREPRISE_ID' => $a->ID, 'DEBUT_GARDE' => '2026-11-28', 'FIN_GARDE' => '2026-12-04', 'UTILISATEUR' => $admin->id,
        ]);

        // Retrait unitaire
        $garde = PharmacieGarde::where('ENTREPRISE_ID', $b->ID)->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.gardes.destroy', $garde->ID))->assertSessionHas('success');
        $this->assertSame(1, PharmacieGarde::count());
    }

    public function test_la_fin_doit_suivre_le_debut(): void
    {
        $this->actingAs($this->admin())->post(route('admin.periodes.store'), ['debut' => '2026-12-04', 'fin' => '2026-11-28'])
            ->assertSessionHasErrors('fin');
    }

    public function test_vider_une_periode_ne_touche_pas_les_autres(): void
    {
        $admin = $this->admin();
        $commune = Commune::create(['DESIGNATION' => 'Yopougon', 'LIEN' => 'yopougon']);
        $ph = $this->pharmacie('Pharmacie Siporex', $commune);
        $p1 = PeriodeGarde::create(['DEBUT' => '2026-11-21', 'FIN' => '2026-11-27']);
        $p2 = PeriodeGarde::create(['DEBUT' => '2026-11-28', 'FIN' => '2026-12-04']);
        PharmacieGarde::create(['ENTREPRISE_ID' => $ph->ID, 'DEBUT_GARDE' => $p1->DEBUT, 'FIN_GARDE' => $p1->FIN]);
        PharmacieGarde::create(['ENTREPRISE_ID' => $ph->ID, 'DEBUT_GARDE' => $p2->DEBUT, 'FIN_GARDE' => $p2->FIN]);

        $this->actingAs($admin)->delete(route('admin.gardes.vider', $p2->ID))->assertSessionHas('success');

        $this->assertSame(1, PharmacieGarde::count());
        $this->assertDatabaseHas('pharmacie_garde', ['DEBUT_GARDE' => '2026-11-21']);
    }

    public function test_modifier_une_periode_deplace_ses_gardes(): void
    {
        $admin = $this->admin();
        $ph = $this->pharmacie('Pharmacie du Marché', Commune::create(['DESIGNATION' => 'Abobo', 'LIEN' => 'abobo']));
        $p = PeriodeGarde::create(['DEBUT' => '2026-11-21', 'FIN' => '2026-11-27']);
        PharmacieGarde::create(['ENTREPRISE_ID' => $ph->ID, 'DEBUT_GARDE' => $p->DEBUT, 'FIN_GARDE' => $p->FIN]);

        $this->actingAs($admin)->put(route('admin.periodes.update', $p->ID), ['debut' => '2026-11-22', 'fin' => '2026-11-28']);

        $this->assertDatabaseHas('pharmacie_garde', ['ENTREPRISE_ID' => $ph->ID, 'DEBUT_GARDE' => '2026-11-22', 'FIN_GARDE' => '2026-11-28']);
    }
}
