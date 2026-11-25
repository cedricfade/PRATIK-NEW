<?php

namespace Tests\Feature\Admin;

class AccesAdminTest extends AdminTestCase
{
    public function test_un_visiteur_non_connecte_est_redirige_vers_la_connexion(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/gardes')->assertRedirect(route('login'));
    }

    public function test_un_membre_ou_un_annonceur_n_a_pas_acces(): void
    {
        foreach ([7, 5] as $role) {
            $this->actingAs($this->admin($role))->get('/admin')->assertRedirect(route('login'));
        }
    }

    public function test_un_admin_desactive_n_a_pas_acces(): void
    {
        $admin = $this->admin(2);
        $admin->update(['active' => 0]);

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('login'));
    }

    public function test_toutes_les_pages_du_backoffice_s_affichent(): void
    {
        $admin = $this->admin();

        foreach ([
            'dashboard', 'gardes.index', 'periodes.index', 'pharmacies.index', 'entreprises.index', 'entreprises.create',
            'categories.index', 'sous-categories.index', 'communes.index', 'biens.index', 'annonceurs.index',
            'immo-referentiels.index', 'titrologie.index', 'news-web.index', 'videos.index', 'sources.index',
            'papiers.index', 'messages.index', 'users.index',
        ] as $route) {
            $this->actingAs($admin)->get(route('admin.'.$route))->assertOk();
        }
    }
}
