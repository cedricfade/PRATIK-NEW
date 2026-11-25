<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Statistique;
use Illuminate\Http\Response;

/** Compte un clic « appeler » / « écrire » pour les statistiques de l'annonceur. */
class StatsController extends Controller
{
    public function store(int $entreprise, string $type): Response
    {
        abort_unless(Entreprise::whereKey($entreprise)->exists(), 404, 'Entreprise introuvable.');

        Statistique::enregistrer($entreprise, $type === 'appel' ? Statistique::APPEL : Statistique::EMAIL);

        return response()->noContent();
    }
}
