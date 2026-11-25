<?php

namespace App\Http\Controllers\Site\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Anti-spam sans captcha : champ piège invisible + délai minimum de remplissage.
 * À coupler avec <x-site.anti-spam /> dans le formulaire.
 */
trait ProtegeContreSpam
{
    protected function estUnRobot(Request $request): bool
    {
        // 1. Le champ "site_internet" est caché aux humains : s'il est rempli, c'est un robot.
        if (filled($request->input('site_internet'))) {
            return true;
        }

        // 2. Un humain met plus de 3 secondes à remplir un formulaire.
        try {
            $affiche = (int) Crypt::decryptString((string) $request->input('_horodatage'));
        } catch (DecryptException) {
            return true;
        }

        return (time() - $affiche) < 3;
    }
}
