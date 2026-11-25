<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'periode_garde', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PeriodeGarde extends Model
{
    public static function courante(): ?self
    {
        $date = now()->toDateString();

        return static::where('DEBUT', '<=', $date)->where('FIN', '>=', $date)->orderByDesc('ID')->first();
    }

    public function getLibelleAttribute(): string
    {
        $debut = Carbon::parse($this->DEBUT)->locale('fr');
        $fin = Carbon::parse($this->FIN)->locale('fr');

        return 'du '.$debut->isoFormat('dddd D MMMM').' au '.$fin->isoFormat('dddd D MMMM YYYY');
    }
}
