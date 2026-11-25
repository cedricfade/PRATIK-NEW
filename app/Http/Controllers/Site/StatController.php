<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Statistique;
use Illuminate\Http\Response;

/** Clics "appeler" / "écrire" comptés pour les statistiques des annonceurs. */
class StatController extends Controller
{
    public function appel(int $entreprise): Response
    {
        return $this->compter($entreprise, Statistique::APPEL);
    }

    public function email(int $entreprise): Response
    {
        return $this->compter($entreprise, Statistique::EMAIL);
    }

    private function compter(int $entreprise, int $type): Response
    {
        if (Entreprise::whereKey($entreprise)->exists()) {
            Statistique::enregistrer($entreprise, $type);
        }

        return response()->noContent();
    }
}
