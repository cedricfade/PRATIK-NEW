<?php

namespace App\Http\Controllers\Immobilier\Pro;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Immobilier\HomeController;
use App\Http\Controllers\Immobilier\Support\Annonceurs;
use App\Models\Annuaire;
use App\Models\Entreprise;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Profil de l'annonceur : société (fiche annuaire), coordonnées, logo, réseaux. */
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load([
            'entreprise:ID,TITRE,SIGLE,LIEN,COMMUNE_ID',
            'entreprise.annuaire',
        ]);

        return view('immobilier.pro.profil', [
            'user' => $user,
            'entreprise' => $user->entreprise,
            'annuaire' => $user->entreprise?->annuaire,
            'logo' => Annonceurs::logoUrl($user->entreprise?->annuaire?->LOGO),
            'communes' => HomeController::communes(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $aEntreprise = (bool) $user->entreprise_id && Entreprise::whereKey($user->entreprise_id)->exists();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'agent_tel' => ['nullable', 'string', 'max:50'],
            'agent_cel' => ['nullable', 'string', 'max:50'],
            'agent_facebook' => ['nullable', 'url', 'max:1000'],
            'agent_instagram' => ['nullable', 'url', 'max:1000'],
            'agent_youtube' => ['nullable', 'url', 'max:1000'],
            'agent_twitter' => ['nullable', 'url', 'max:1000'],
        ] + ($aEntreprise ? [
            'titre' => ['required', 'string', 'max:300'],
            'sigle' => ['nullable', 'string', 'max:100'],
            'commune_id' => ['required', 'integer', 'exists:communes,ID'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'tel_1' => ['nullable', 'string', 'max:100'],
            'tel_2' => ['nullable', 'string', 'max:100'],
            'cel_1' => ['nullable', 'string', 'max:100'],
            'cel_2' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:100'],
            'email_1' => ['nullable', 'email', 'max:100'],
            'email_2' => ['nullable', 'email', 'max:100'],
            'site' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ] : []), [], ['name' => 'nom du compte', 'commune_id' => 'commune', 'cel_1' => 'mobile', 'email_1' => 'e-mail']);

        $nouveauLogo = null;

        DB::transaction(function () use ($request, $user, $data, $aEntreprise, &$nouveauLogo) {
            $user->update(collect($data)->only(['name', 'agent_tel', 'agent_cel', 'agent_facebook', 'agent_instagram', 'agent_youtube', 'agent_twitter'])->all());

            if (! $aEntreprise) {
                return;
            }

            Entreprise::whereKey($user->entreprise_id)->update([
                'TITRE' => $data['titre'],
                'SIGLE' => $data['sigle'] ?? null,
                'COMMUNE_ID' => (int) $data['commune_id'],
            ]);

            $annuaire = Annuaire::firstOrCreate(['ENTREPRISE_ID' => $user->entreprise_id]);
            $colonnes = [
                'ADRESSE' => $data['adresse'] ?? null,
                'TEL_1' => $data['tel_1'] ?? null,
                'TEL_2' => $data['tel_2'] ?? null,
                'CEL_1' => $data['cel_1'] ?? null,
                'CEL_2' => $data['cel_2'] ?? null,
                'WHATSAPP' => $data['whatsapp'] ?? null,
                'EMAIL_1' => $data['email_1'] ?? null,
                'EMAIL_2' => $data['email_2'] ?? null,
                'SITE' => $data['site'] ?? null,
                'DESCRIPTION' => $data['description'] ?? null,
            ];

            if ($request->hasFile('logo')) {
                // L'ancien logo est supprimé du disque par le modèle Annuaire (s'il n'est plus utilisé).
                $colonnes['LOGO'] = $nouveauLogo = Media::store($request->file('logo'), 'logos', '', 600);
            }

            $annuaire->update($colonnes);
        });

        Annonceurs::oublier();

        return back()->with('success', 'Profil mis à jour.');
    }
}
