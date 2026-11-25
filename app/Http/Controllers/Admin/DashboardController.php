<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annuaire;
use App\Models\Contact;
use App\Models\ContactEntreprise;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\News;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $jour = now()->toDateString();

        $kpi = Cache::remember('admin:kpi:'.$jour, 600, fn () => [
            'entreprises' => Entreprise::fichesAnnuaire()->count(),
            'clients' => Annuaire::where('VIP', 1)->count(),
            'biens_attente' => Immobilier::where(fn ($q) => $q->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION'))->count(),
            'biens_publies' => Immobilier::publie()->count(),
            'messages_semaine' => Contact::where('date', '>=', now()->subDays(7)->toDateString())->count(),
            'messages_entreprises' => ContactEntreprise::where('status', 0)->count(),
            'gardes' => PharmacieGarde::enCours($jour)->count(),
            'unes' => News::titrologie()->where('DATE_PARUTION', $jour)->count(),
        ]);

        $periode = PeriodeGarde::courante();
        $prochaine = PeriodeGarde::where('DEBUT', '>', $jour)->orderBy('DEBUT')->first();
        $prochaineCount = $prochaine
            ? PharmacieGarde::where('DEBUT_GARDE', $prochaine->DEBUT)->where('FIN_GARDE', $prochaine->FIN)->count()
            : 0;

        $derniersBiens = Immobilier::where(fn ($q) => $q->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION'))
            ->with('entreprise:ID,TITRE,LIEN')
            ->orderByDesc('ID')
            ->limit(5)
            ->get(['ID', 'ENTREPRISE_ID', 'PRIX', 'TYPE_IMMOBILIER_ID', 'DATE_LOG']);

        $derniersMessages = Contact::orderByDesc('id')->limit(5)->get(['id', 'nom', 'objet', 'categorie', 'date']);

        return view('admin.dashboard', compact('kpi', 'periode', 'prochaine', 'prochaineCount', 'derniersBiens', 'derniersMessages'));
    }
}
