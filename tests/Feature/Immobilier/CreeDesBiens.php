<?php

namespace Tests\Feature\Immobilier;

use App\Models\Annuaire;
use App\Models\CategorieImmobilier;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Fabrique des annonceurs et des biens dans la base de test. */
trait CreeDesBiens
{
    protected Commune $cocody;

    protected Commune $yopougon;

    protected CategorieImmobilier $villa;

    protected CategorieImmobilier $appartement;

    protected function preparerReferentiels(): void
    {
        $this->cocody = Commune::create(['DESIGNATION' => 'Cocody', 'LIEN' => 'cocody']);
        $this->yopougon = Commune::create(['DESIGNATION' => 'Yopougon', 'LIEN' => 'yopougon']);
        $this->appartement = CategorieImmobilier::create(['DESIGNATION' => 'Appartement', 'ACTIVATION' => 1]);
        $this->villa = CategorieImmobilier::create(['DESIGNATION' => 'Villa duplex', 'ACTIVATION' => 1]);

        foreach ([1 => 'Agence Location', 2 => 'Agence Vente', 3 => 'Promoteur Vente', 4 => 'Promoteur Amenageur Foncier'] as $id => $nom) {
            DB::table('type_immobilier')->insert(['ID' => $id, 'DESIGNATION' => $nom, 'ACTIVATION' => 1]);
        }
    }

    protected function annonceur(string $nom = 'Agence Test', array $attributs = []): User
    {
        $societe = Entreprise::create([
            'TITRE' => $nom,
            'LIEN' => \Illuminate\Support\Str::slug($nom),
            'TYPE_ID' => 1,
            'COMMUNE_ID' => $this->cocody->ID,
        ]);
        Annuaire::create([
            'ENTREPRISE_ID' => $societe->ID,
            'CEL_1' => '07 07 07 07 07',
            'TEL_1' => '27 22 00 00 00',
            'EMAIL_1' => \Illuminate\Support\Str::slug($nom).'@exemple.ci',
        ]);

        return User::create($attributs + [
            'name' => $nom,
            'email' => \Illuminate\Support\Str::slug($nom).'-compte@exemple.ci',
            'password' => 'secret-secret',
            'role_id' => 5,
            'CATEGORIE' => 'Immobilier',
            'entreprise_id' => $societe->ID,
            'active' => 1,
        ]);
    }

    protected function bien(User $auteur, array $attributs = [], array $fiche = []): Immobilier
    {
        $titre = $fiche['TITRE'] ?? 'Villa test '.uniqid();
        $entreprise = Entreprise::create($fiche + [
            'TITRE' => $titre,
            'LIEN' => \Illuminate\Support\Str::slug($titre),
            'TYPE_ID' => 3,
            'COMMUNE_ID' => $this->cocody->ID,
            'UTILISATEUR' => $auteur->id,
            'COMPTEUR' => 0,
        ]);

        return Immobilier::create($attributs + [
            'ENTREPRISE_ID' => $entreprise->ID,
            'TYPE_IMMOBILIER_ID' => 2,
            'CATEGORIE_IMMOBILIER_ID' => $this->villa->ID,
            'PRIX' => '50000000',
            'VALIDATION' => 'OUI',
            'UTILISATEUR' => $auteur->id,
            'ILLUSTATION' => 'imm_default.jpg',
            'CHAMBRE' => 3,
        ]);
    }
}
