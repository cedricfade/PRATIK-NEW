<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Paramètres clé → valeur réglables depuis le backoffice, mis en cache. */
#[Table(name: 'parametres_site', key: 'cle', keyType: 'string', incrementing: false)]
#[Fillable(['cle', 'valeur'])]
class ParametreSite extends Model
{
    public const CREATED_AT = null;

    private const CACHE = 'parametres-site';

    /** @return array<string, string|null> */
    public static function tous(): array
    {
        return Cache::rememberForever(self::CACHE, function () {
            if (! Schema::hasTable('parametres_site')) {
                return [];
            }

            return static::query()->pluck('valeur', 'cle')->all();
        });
    }

    public static function valeur(string $cle, ?string $defaut = null): ?string
    {
        $valeur = self::tous()[$cle] ?? null;

        return ($valeur === null || $valeur === '') ? $defaut : $valeur;
    }

    /** @param array<string, string|null> $valeurs */
    public static function enregistrer(array $valeurs): void
    {
        foreach ($valeurs as $cle => $valeur) {
            static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }

        Cache::forget(self::CACHE);
    }
}
