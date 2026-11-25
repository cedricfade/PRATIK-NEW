<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/** Base commune : URLs absolues, dates ISO 8601, nettoyage des valeurs héritées. */
abstract class ApiResource extends JsonResource
{
    public static function urlAbsolue(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return str_starts_with($url, 'http') ? $url : url($url);
    }

    public static function date(mixed $valeur): ?string
    {
        if (! $valeur || str_starts_with((string) $valeur, '0000')) {
            return null;
        }

        try {
            return Carbon::parse($valeur)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function dateHeure(mixed $valeur): ?string
    {
        if (! $valeur) {
            return null;
        }

        try {
            return Carbon::parse($valeur)->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Chaîne vide / "NULL" / "0" → null. */
    public static function texte(mixed $valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return in_array($valeur, ['', 'NULL', '0'], true) ? null : $valeur;
    }

    public static function coordonnee(mixed $valeur): ?float
    {
        $f = (float) $valeur;

        return $f === 0.0 ? null : round($f, 7);
    }

    public static function oui(mixed $valeur): bool
    {
        return in_array(strtoupper(trim((string) $valeur)), ['OUI', '1', 'ON', 'YES'], true);
    }
}
