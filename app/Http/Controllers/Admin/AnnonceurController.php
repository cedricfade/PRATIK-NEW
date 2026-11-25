<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnonceurRequest;
use App\Models\Annuaire;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Comptes professionnels (role 5) qui publient des biens depuis l'espace annonceur. */
class AnnonceurController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $annonceurs = User::query()
            ->whereIn('role_id', [config('pratik.roles.immobilier'), config('pratik.roles.restauration')])
            ->when($recherche !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$recherche}%")->orWhere('email', 'like', "%{$recherche}%")))
            ->with(['entreprise:ID,TITRE,LIEN', 'entreprise.annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1'])
            ->withCount('biens')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $publies = Immobilier::whereIn('UTILISATEUR', $annonceurs->pluck('id'))
            ->where('VALIDATION', 'OUI')
            ->groupBy('UTILISATEUR')
            ->get(['UTILISATEUR', DB::raw('COUNT(*) as total')])
            ->pluck('total', 'UTILISATEUR');

        return view('admin.immobilier.annonceurs', [
            'annonceurs' => $annonceurs,
            'publies' => $publies,
            'recherche' => $recherche,
            'communes' => Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function store(AnnonceurRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $entrepriseId = $request->input('entreprise_id');

            if (! $entrepriseId) {
                $entreprise = Entreprise::create([
                    'TITRE' => $request->input('nouvelle_entreprise'),
                    'COMMUNE_ID' => $request->input('commune_id'),
                    'TYPE_ID' => config('pratik.types.annuaire'),
                    'TYPE' => 1,
                    'PRIORITE' => 0,
                    'COMPTEUR' => 0,
                    'ACTIVATION' => 1,
                    'DATE_LOG' => now()->toDateString(),
                    'UTILISATEUR' => $request->user()->id,
                    'LIEN' => 'tmp-'.uniqid(),
                ]);
                $entreprise->update(['LIEN' => Entreprise::lienUnique($entreprise->TITRE, $entreprise->ID)]);
                Annuaire::create([
                    'ENTREPRISE_ID' => $entreprise->ID,
                    'CLIENT' => 'OUI',
                    'VIP' => 0,
                    'TEL_1' => $request->input('agent_tel'),
                    'CEL_1' => $request->input('agent_cel'),
                    'EMAIL_1' => $request->input('email'),
                ]);
                $entrepriseId = $entreprise->ID;
            }

            User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'role_id' => $request->input('categorie') === 'Restaurant' ? config('pratik.roles.restauration') : config('pratik.roles.immobilier'),
                'entreprise_id' => $entrepriseId,
                'CATEGORIE' => $request->input('categorie'),
                'agent_tel' => $request->input('agent_tel'),
                'agent_cel' => $request->input('agent_cel'),
                'active' => 1,
                'user_created' => (string) $request->user()->id,
            ]);
        });

        return back()->with('success', 'Compte annonceur créé. Communiquez-lui son e-mail et son mot de passe.');
    }

    public function activation(User $user): RedirectResponse
    {
        abort_unless($user->estPro(), 404);
        $user->update(['active' => $user->estActif() ? 0 : 1]);

        return back()->with('success', $user->estActif() ? 'Compte réactivé.' : 'Compte désactivé : l\'annonceur ne peut plus se connecter.');
    }

    public function password(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->estPro(), 404);
        $request->validate(['password' => ['required', 'string', Password::min(8)]]);
        $user->update(['password' => $request->input('password')]);

        return back()->with('success', 'Mot de passe de '.$user->name.' modifié.');
    }
}
