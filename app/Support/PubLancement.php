<?php

namespace App\Support;

use App\Models\ParametreSite;
use Carbon\Carbon;

/**
 * Publicité plein écran affichée à l'ouverture de l'application mobile
 * (Admin › Apparence › Application mobile). Image stockée dans images/Heros.
 */
class PubLancement
{
    public const CLES = ['actif', 'image', 'lien', 'duree', 'debut', 'fin'];

    /** @return array<string, string|null> réglages bruts */
    public static function reglages(): array
    {
        return collect(self::CLES)->mapWithKeys(fn ($c) => [$c => ParametreSite::valeur("app.pub.$c")])->all();
    }

    /** Publicité à afficher maintenant, ou null (désactivée, sans image ou hors période). */
    public static function active(): ?array
    {
        $r = self::reglages();

        if ($r['actif'] !== '1' || ! $r['image']) {
            return null;
        }

        $aujourdhui = now()->toDateString();
        if (($r['debut'] && $aujourdhui < $r['debut']) || ($r['fin'] && $aujourdhui > $r['fin'])) {
            return null;
        }

        return [
            'image_url' => Media::url('heros', $r['image']),
            'lien' => $r['lien'] ?: null,
            'duree' => max(2, min(15, (int) ($r['duree'] ?: 5))),
            'fin' => $r['fin'] ? Carbon::parse($r['fin'])->toDateString() : null,
            // change quand l'image change : l'app peut s'en servir pour son cache
            'version' => md5((string) $r['image']),
        ];
    }
}
