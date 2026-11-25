<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_inscription_cree_un_visiteur_et_renvoie_un_jeton(): void
    {
        $reponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Awa Koné',
            'email' => 'awa@example.ci',
            'telephone' => '0707070707',
            'password' => 'motdepasse1',
            'password_confirmation' => 'motdepasse1',
            'device_name' => 'Pixel 8',
        ]);

        $reponse->assertCreated()
            ->assertJsonStructure(['token', 'token_type', 'expires_at', 'user' => ['id', 'name', 'email', 'telephone', 'role' => ['code', 'libelle']]])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.role.code', 'visiteur')
            ->assertJsonPath('user.telephone', '0707070707');

        $this->assertDatabaseHas('users', ['email' => 'awa@example.ci', 'role_id' => 7, 'CATEGORIE' => 'Visiteur', 'active' => 1]);
    }

    public function test_inscription_refuse_un_email_deja_utilise(): void
    {
        $this->utilisateur(['email' => 'pris@example.ci']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'X', 'email' => 'pris@example.ci', 'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_connexion_puis_profil_puis_deconnexion(): void
    {
        $user = $this->utilisateur(['email' => 'moussa@example.ci', 'password' => Hash::make('secret123')]);

        $jeton = $this->postJson('/api/v1/auth/login', ['email' => 'moussa@example.ci', 'password' => 'secret123', 'device_name' => 'iPhone'])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->json('token');

        $this->withToken($jeton)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', 'moussa@example.ci');

        $this->withToken($jeton)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($jeton)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_mauvais_mot_de_passe(): void
    {
        $this->utilisateur(['email' => 'a@example.ci', 'password' => Hash::make('bonmotdepasse')]);

        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.ci', 'password' => 'mauvais'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Adresse e-mail ou mot de passe incorrect.');
    }

    public function test_compte_desactive_refuse(): void
    {
        $this->utilisateur(['email' => 'off@example.ci', 'password' => Hash::make('secret123'), 'active' => 0]);

        $this->postJson('/api/v1/auth/login', ['email' => 'off@example.ci', 'password' => 'secret123'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Ce compte est désactivé. Contactez Pratik pour le réactiver.');
    }

    public function test_un_pro_existant_se_connecte_avec_son_role(): void
    {
        $pro = $this->pro(['email' => 'agence@example.ci', 'password' => Hash::make('secret123')]);

        $this->postJson('/api/v1/auth/login', ['email' => 'agence@example.ci', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonPath('user.role.code', 'pro')
            ->assertJsonPath('user.entreprise_id', $pro->entreprise_id);
    }

    public function test_route_protegee_sans_jeton(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_modifier_profil_et_mot_de_passe(): void
    {
        $user = $this->utilisateur(['password' => Hash::make('ancien123')]);
        $entetes = $this->enTetes($user);

        $this->putJson('/api/v1/me', ['name' => 'Nouveau Nom', 'telephone' => '0101010101'], $entetes)
            ->assertOk()
            ->assertJsonPath('data.name', 'Nouveau Nom')
            ->assertJsonPath('data.telephone', '0101010101');

        $this->putJson('/api/v1/me/password', ['current_password' => 'faux', 'password' => 'nouveau123', 'password_confirmation' => 'nouveau123'], $entetes)
            ->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->putJson('/api/v1/me/password', ['current_password' => 'ancien123', 'password' => 'nouveau123', 'password_confirmation' => 'nouveau123'], $entetes)
            ->assertOk();

        $this->assertTrue(Hash::check('nouveau123', User::find($user->id)->password));
    }

    public function test_mot_de_passe_oublie_repond_toujours_pareil(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'inconnu@example.ci'])
            ->assertOk()
            ->assertJsonStructure(['message']);
    }
}
