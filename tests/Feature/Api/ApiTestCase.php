<?php

namespace Tests\Feature\Api;

use App\Models\Annuaire;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

/** Outils communs aux tests de l'API : client Passport, fabrication de données héritées. */
abstract class ApiTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(ClientRepository::class)->createPersonalAccessGrantClient('Tests', 'users');
    }

    protected function jetonPour(User $user): string
    {
        return $user->createToken('tests')->accessToken;
    }

    /** @return array<string, string> */
    protected function enTetes(User $user): array
    {
        return ['Authorization' => 'Bearer '.$this->jetonPour($user), 'Accept' => 'application/json'];
    }

    protected function utilisateur(array $attributs = []): User
    {
        return User::factory()->create($attributs + ['role_id' => config('pratik.roles.visiteur'), 'active' => 1]);
    }

    protected function pro(array $attributs = []): User
    {
        $fiche = $this->entreprise(['TITRE' => 'Agence Test '.Str::random(4)]);

        return User::factory()->create($attributs + [
            'role_id' => config('pratik.roles.immobilier'),
            'CATEGORIE' => 'Immobilier',
            'entreprise_id' => $fiche->ID,
            'active' => 1,
        ]);
    }

    protected function commune(string $nom = 'Cocody', ?int $id = null): Commune
    {
        return Commune::forceCreate(array_filter(['ID' => $id, 'DESIGNATION' => $nom, 'LIEN' => Str::slug($nom)]));
    }

    protected function entreprise(array $attributs = [], array $annuaire = []): Entreprise
    {
        $titre = $attributs['TITRE'] ?? 'Entreprise '.Str::random(6);
        $entreprise = Entreprise::create($attributs + [
            'TITRE' => $titre,
            'LIEN' => Str::slug($titre).'-'.Str::lower(Str::random(4)),
            'TYPE_ID' => config('pratik.types.annuaire'),
            'PRIORITE' => 0,
            'COMPTEUR' => 0,
        ]);
        Annuaire::create($annuaire + ['ENTREPRISE_ID' => $entreprise->ID, 'CLIENT' => 'NON', 'VIP' => 0]);

        return $entreprise;
    }

    protected function bien(User $auteur, array $attributs = [], array $fiche = []): Immobilier
    {
        $titre = $fiche['TITRE'] ?? 'Villa '.Str::random(5);
        $entreprise = Entreprise::create($fiche + [
            'TITRE' => $titre,
            'LIEN' => Str::slug($titre).'-'.Str::lower(Str::random(4)),
            'TYPE_ID' => config('pratik.types.immobilier'),
            'UTILISATEUR' => $auteur->id,
            'COMPTEUR' => 0,
        ]);

        return Immobilier::create($attributs + [
            'ENTREPRISE_ID' => $entreprise->ID,
            'UTILISATEUR' => $auteur->id,
            'TYPE_IMMOBILIER_ID' => 2,
            'PRIX' => '50000000',
            'VALIDATION' => 'OUI',
            'ILLUSTATION' => Immobilier::IMAGE_DEFAUT,
        ]);
    }
}
