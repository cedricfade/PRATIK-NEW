<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/** Compteurs mensuels par entreprise. TYPE_STAT : 1 appels, 2 e-mails, 3 vues, 4 horaires des vols. */
#[Table(name: 'statistiques', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Statistique extends Model
{
    public const APPEL = 1;

    public const EMAIL = 2;

    public const VUE = 3;

    public const VOLS = 4;

    public static function enregistrer(?int $entrepriseId, int $type): void
    {
        $now = now();

        $ligne = static::query()
            ->when($entrepriseId, fn ($q) => $q->where('ENTREPRISE_ID', $entrepriseId), fn ($q) => $q->whereNull('ENTREPRISE_ID'))
            ->where('TYPE_STAT', $type)
            ->where('MOIS', (string) $now->month)
            ->where('ANNEE', (string) $now->year)
            ->first();

        if ($ligne) {
            static::whereKey($ligne->ID)->increment('STATS');

            return;
        }

        static::create([
            'ENTREPRISE_ID' => $entrepriseId,
            'TYPE_STAT' => $type,
            'MOIS' => (string) $now->month,
            'ANNEE' => (string) $now->year,
            'STATS' => 1,
        ]);
    }
}
