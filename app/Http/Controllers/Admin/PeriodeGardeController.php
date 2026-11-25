<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PeriodeGardeRequest;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeriodeGardeController extends Controller
{
    use VideCachesPublics;

    public function index(): View
    {
        $periodes = PeriodeGarde::orderByDesc('DEBUT')->orderByDesc('ID')->paginate(20);

        // Nombre de gardes par période, en une seule requête.
        $comptes = PharmacieGarde::query()
            ->whereIn(DB::raw("CONCAT(DEBUT_GARDE, '|', FIN_GARDE)"), $periodes->map(fn ($p) => $p->DEBUT.'|'.$p->FIN)->all())
            ->groupBy('DEBUT_GARDE', 'FIN_GARDE')
            ->get(['DEBUT_GARDE', 'FIN_GARDE', DB::raw('COUNT(*) as total')])
            ->mapWithKeys(fn ($r) => [$r->DEBUT_GARDE.'|'.$r->FIN_GARDE => $r->total]);

        // Proposition : la semaine qui suit la dernière période.
        $derniere = PeriodeGarde::orderByDesc('FIN')->first();
        $suggestion = $derniere
            ? ['debut' => \Carbon\Carbon::parse($derniere->FIN)->addDay()->toDateString(), 'fin' => \Carbon\Carbon::parse($derniere->FIN)->addDays(7)->toDateString()]
            : ['debut' => now()->toDateString(), 'fin' => now()->addDays(6)->toDateString()];

        return view('admin.gardes.periodes', compact('periodes', 'comptes', 'suggestion'));
    }

    public function store(PeriodeGardeRequest $request): RedirectResponse
    {
        $existe = PeriodeGarde::where('DEBUT', $request->date('debut')->toDateString())
            ->where('FIN', $request->date('fin')->toDateString())
            ->first();

        $periode = $existe ?? PeriodeGarde::create([
            'DEBUT' => $request->date('debut')->toDateString(),
            'FIN' => $request->date('fin')->toDateString(),
        ]);

        $this->viderCacheGardes();

        return redirect()->route('admin.gardes.index', ['periode' => $periode->ID])
            ->with('success', $existe ? 'Cette période existait déjà : vous pouvez y affecter les pharmacies.' : 'Période créée. Cochez maintenant les pharmacies de garde.');
    }

    public function update(PeriodeGardeRequest $request, PeriodeGarde $periode): RedirectResponse
    {
        $debut = $request->date('debut')->toDateString();
        $fin = $request->date('fin')->toDateString();

        DB::transaction(function () use ($periode, $debut, $fin) {
            // Les gardes rattachées suivent les nouvelles dates.
            PharmacieGarde::where('DEBUT_GARDE', $periode->DEBUT)->where('FIN_GARDE', $periode->FIN)
                ->update(['DEBUT_GARDE' => $debut, 'FIN_GARDE' => $fin]);
            $periode->update(['DEBUT' => $debut, 'FIN' => $fin]);
        });

        $this->viderCacheGardes();

        return back()->with('success', 'Période modifiée (les gardes associées ont suivi).');
    }

    public function destroy(PeriodeGarde $periode): RedirectResponse
    {
        $autres = PeriodeGarde::whereKeyNot($periode->ID)->where('DEBUT', $periode->DEBUT)->where('FIN', $periode->FIN)->exists();
        $gardes = PharmacieGarde::where('DEBUT_GARDE', $periode->DEBUT)->where('FIN_GARDE', $periode->FIN)->count();

        if ($gardes > 0 && ! $autres) {
            return back()->with('error', "Cette période contient {$gardes} gardes : videz-la d'abord depuis l'écran des gardes.");
        }

        $periode->delete();
        $this->viderCacheGardes();

        return back()->with('success', 'Période supprimée.');
    }
}
