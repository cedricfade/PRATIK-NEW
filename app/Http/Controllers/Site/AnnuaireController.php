<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ProtegeContreSpam;
use App\Models\Annuaire;
use App\Models\CategorieAnnuaire;
use App\Models\Commune;
use App\Models\ContactEntreprise;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AnnuaireController extends Controller
{
    use ProtegeContreSpam;

    /** Colonnes utiles pour les cartes entreprise (évite de charger les longues descriptions). */
    private const COLONNES_CARTE = ['entreprises.ID', 'entreprises.TITRE', 'entreprises.SIGLE', 'entreprises.LIEN', 'entreprises.ACTIVITE', 'entreprises.COMMUNE_ID', 'entreprises.PRIORITE'];

    private const RELATIONS_CARTE = [
        'annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,CLIENT,VIP,TEL_1,TEL_2,CEL_1,CEL_2',
        'commune:ID,DESIGNATION,LIEN',
    ];

    public function index(): View
    {
        $categories = Cache::remember('annuaire:categories', 21600, function () {
            $totaux = ThematiqueAnnuaire::query()
                ->groupBy('CATEGORIE_ID')
                ->pluck(DB::raw('COUNT(DISTINCT ENTREPRISE_ID)'), 'CATEGORIE_ID');

            return CategorieAnnuaire::orderBy('DESIGNATION')
                ->get(['ID', 'DESIGNATION', 'DESCRIPTION', 'LIEN'])
                ->each(fn ($c) => $c->setAttribute('total', (int) ($totaux[$c->ID] ?? 0)));
        });

        $communes = $this->communes();

        return view('site.annuaire.index', compact('categories', 'communes'));
    }

    public function categorie(string $cat): View
    {
        $categorie = CategorieAnnuaire::where('LIEN', $cat)->firstOrFail();

        $sousCategories = Cache::remember("annuaire:categorie:{$categorie->ID}:sous", 21600, function () use ($categorie) {
            $totaux = ThematiqueAnnuaire::where('CATEGORIE_ID', $categorie->ID)
                ->groupBy('SOUS_CATEGORIE_ID')
                ->pluck(DB::raw('COUNT(DISTINCT ENTREPRISE_ID)'), 'SOUS_CATEGORIE_ID');

            return SousCategorie::where('CATEGORIE_ID', $categorie->ID)
                ->where('ACTIVATION', 1)
                ->orderBy('DESIGNATION')
                ->get(['ID', 'CATEGORIE_ID', 'DESIGNATION', 'LIEN'])
                ->each(fn ($s) => $s->setAttribute('total', (int) ($totaux[$s->ID] ?? 0)));
        });

        // Clients Pratik de la catégorie, avec logo : renouvelés toutes les 30 minutes.
        $clients = Cache::remember("annuaire:categorie:{$categorie->ID}:clients", 1800, fn () => $this->clientsAvecLogo(
            fn (Builder $q) => $q->whereIn('entreprises.ID', ThematiqueAnnuaire::select('ENTREPRISE_ID')->where('CATEGORIE_ID', $categorie->ID)),
            6,
        ));

        return view('site.annuaire.categorie', compact('categorie', 'sousCategories', 'clients'));
    }

    public function sousCategorie(Request $request, string $cat, string $sous): View
    {
        $categorie = CategorieAnnuaire::where('LIEN', $cat)->firstOrFail();
        $sousCategorie = SousCategorie::where('LIEN', $sous)->where('CATEGORIE_ID', $categorie->ID)->first()
            ?? SousCategorie::where('LIEN', $sous)->firstOrFail();

        $communes = $this->communes();
        $commune = $request->filled('commune') ? $communes->firstWhere('LIEN', $request->query('commune')) : null;

        // TOP 12 : jusqu'à 12 pros (fiches VIP) de la rubrique, tirés au hasard.
        // Le tirage est gardé une heure pour que la pagination en dessous reste stable.
        $top12 = Cache::remember("annuaire:top12:{$sousCategorie->ID}:".($commune->ID ?? 0), 3600, fn () => Entreprise::query()
            ->select(self::COLONNES_CARTE)
            ->whereIn('entreprises.ID', ThematiqueAnnuaire::select('ENTREPRISE_ID')->where('SOUS_CATEGORIE_ID', $sousCategorie->ID))
            ->whereIn('entreprises.ID', Annuaire::select('ENTREPRISE_ID')->where('VIP', 1))
            ->where('entreprises.LIEN', '!=', '')
            ->when($commune, fn ($q) => $q->where('entreprises.COMMUNE_ID', $commune->ID))
            ->with(self::RELATIONS_CARTE)
            ->inRandomOrder()
            ->limit(12)
            ->get());

        $entreprises = Entreprise::query()
            ->select(self::COLONNES_CARTE)
            ->whereIn('entreprises.ID', ThematiqueAnnuaire::select('ENTREPRISE_ID')->where('SOUS_CATEGORIE_ID', $sousCategorie->ID))
            ->whereNotIn('entreprises.ID', $top12->pluck('ID')->all() ?: [0])
            ->where('entreprises.LIEN', '!=', '')
            ->when($commune, fn ($q) => $q->where('entreprises.COMMUNE_ID', $commune->ID))
            ->orderByRaw($this->ordreClientsDabord())
            ->orderByDesc('entreprises.PRIORITE')
            ->orderBy('entreprises.TITRE')
            ->with(self::RELATIONS_CARTE)
            ->paginate(20)
            ->withQueryString();

        return view('site.annuaire.sous-categorie', compact('categorie', 'sousCategorie', 'entreprises', 'communes', 'commune', 'top12'));
    }

    public function fiche(string $lien): View
    {
        $entreprise = Entreprise::query()
            ->where('entreprises.LIEN', $lien)
            ->whereHas('annuaire')
            ->orderByRaw('entreprises.TYPE_ID = ? DESC', [config('pratik.types.annuaire')])
            ->with(['commune:ID,DESIGNATION,LIEN', 'annuaire', 'annuaire.photos', 'annuaire.nature'])
            ->firstOrFail();

        $annuaire = $entreprise->annuaire;

        $thematiques = ThematiqueAnnuaire::where('ENTREPRISE_ID', $entreprise->ID)
            ->with(['sousCategorie:ID,CATEGORIE_ID,DESIGNATION,LIEN', 'sousCategorie.categorie:ID,DESIGNATION,LIEN'])
            ->get()
            ->filter(fn ($t) => $t->sousCategorie && $t->sousCategorie->categorie)
            ->unique('SOUS_CATEGORIE_ID')
            ->values();

        $succursales = Annuaire::where('SIEGE_ID', $entreprise->ID)
            ->where('ENTREPRISE_ID', '!=', $entreprise->ID)
            ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'entreprise.commune:ID,DESIGNATION'])
            ->limit(30)
            ->get(['ID', 'ENTREPRISE_ID', 'ADRESSE', 'TEL_1', 'CEL_1'])
            ->filter(fn ($s) => $s->entreprise && $s->entreprise->LIEN);

        $siege = $annuaire->SIEGE_ID && (int) $annuaire->SIEGE_ID !== (int) $entreprise->ID
            ? Entreprise::whereKey($annuaire->SIEGE_ID)->first(['ID', 'TITRE', 'LIEN'])
            : null;

        $departements = Departement::where('ANNUAIRE_ID', $annuaire->ID)->get();

        $annonceur = User::where('entreprise_id', $entreprise->ID)->first(['id']);
        $biens = $annonceur
            ? Immobilier::publie()
                ->where('UTILISATEUR', $annonceur->id)
                ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'entreprise.commune:ID,DESIGNATION', 'categorie:ID,DESIGNATION'])
                ->orderByDesc('ID')
                ->limit(3)
                ->get()
            : collect();

        $entreprise->incrementerVues();

        $categoriePrincipale = $thematiques->first()?->sousCategorie;

        return view('site.annuaire.fiche', compact('entreprise', 'annuaire', 'thematiques', 'succursales', 'siege', 'departements', 'biens', 'categoriePrincipale'));
    }

    public function contacter(Request $request, Entreprise $entreprise): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'prenom' => ['nullable', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
            'objet' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
        ], [], ['contact' => 'téléphone']);

        $retour = back()->with('success', 'Votre message a bien été transmis à '.$entreprise->TITRE.'.');

        if ($this->estUnRobot($request)) {
            return $retour;
        }

        $annuaire = Annuaire::where('ENTREPRISE_ID', $entreprise->ID)->first(['ID', 'EMAIL_1', 'EMAIL_2']);
        $emailEntreprise = $annuaire?->emails[0] ?? null;

        ContactEntreprise::create([
            'entreprise_id' => $entreprise->ID,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'] ?? '',
            'contact' => $data['contact'],
            'email' => $data['email'],
            'objet' => $data['objet'],
            'contactmessage' => $data['message'],
            'emailEntreprise' => $emailEntreprise,
            'status' => 0,
        ]);

        if ($emailEntreprise) {
            try {
                Mail::raw(
                    "Bonjour,\n\nVous avez reçu un message via votre fiche Pratik ({$entreprise->TITRE}).\n\n"
                    ."De : {$data['nom']} ".($data['prenom'] ?? '')."\nTéléphone : {$data['contact']}\nE-mail : {$data['email']}\n"
                    ."Objet : {$data['objet']}\n\n{$data['message']}\n\n— Pratik, l'annuaire pratique de Côte d'Ivoire",
                    fn ($m) => $m->to($emailEntreprise)->replyTo($data['email'], $data['nom'])->subject('Pratik — '.$data['objet']),
                );
            } catch (\Throwable $e) {
                Log::warning('Envoi du message entreprise impossible : '.$e->getMessage());
            }
        }

        return $retour;
    }

    public function recherche(Request $request): View
    {
        $terme = trim((string) ($request->query('q') ?? $request->query('search_box') ?? ''));
        $communes = $this->communes();
        $commune = $request->filled('commune') ? $communes->firstWhere('LIEN', $request->query('commune')) : null;

        $entreprises = null;
        $rubriques = collect();

        if (mb_strlen($terme) >= 2) {
            $entreprises = Entreprise::query()
                ->select(self::COLONNES_CARTE)
                ->fichesAnnuaire()
                ->recherche($terme)
                ->where('entreprises.LIEN', '!=', '')
                ->when($commune, fn ($q) => $q->where('entreprises.COMMUNE_ID', $commune->ID))
                ->orderByRaw($this->ordreClientsDabord())
                ->orderByDesc('entreprises.PRIORITE')
                ->orderBy('entreprises.TITRE')
                ->with(self::RELATIONS_CARTE)
                ->paginate(20)
                ->withQueryString();

            $rubriques = SousCategorie::where('ACTIVATION', 1)
                ->where('DESIGNATION', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $terme).'%')
                ->with('categorie:ID,DESIGNATION,LIEN')
                ->limit(8)
                ->get(['ID', 'CATEGORIE_ID', 'DESIGNATION', 'LIEN'])
                ->filter(fn ($s) => $s->categorie);
        }

        return view('site.annuaire.recherche', compact('terme', 'entreprises', 'rubriques', 'communes', 'commune'));
    }

    /** Pros (fiches VIP) ayant un vrai logo, tirés au hasard. */
    private function clientsAvecLogo(callable $filtre, int $nombre)
    {
        return Entreprise::query()
            ->select(self::COLONNES_CARTE)
            ->tap($filtre)
            ->whereIn('entreprises.ID', Annuaire::select('ENTREPRISE_ID')->where('VIP', 1)->where('LOGO', '!=', '')
                ->whereRaw('LOGO NOT REGEXP ?', ['^(logo_entrprise_default|default-logo-flou[0-9]*|default)\\.']))
            ->where('entreprises.LIEN', '!=', '')
            ->with(self::RELATIONS_CARTE)
            ->inRandomOrder()
            ->limit($nombre)
            ->get();
    }

    /** Les pros (VIP) passent devant. */
    private function ordreClientsDabord(): string
    {
        return '(SELECT MAX(a.VIP = 1) FROM annuaires a WHERE a.ENTREPRISE_ID = entreprises.ID) DESC';
    }

    private function communes()
    {
        return Cache::remember('communes:liste', 86400, fn () => Commune::where('DESIGNATION', '!=', '')->orderBy('DESIGNATION')->get(['ID', 'DESIGNATION', 'LIEN']));
    }
}
