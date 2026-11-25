<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParametreSite;
use App\Models\PubApp;
use App\Support\Media;
use App\Support\PubLancement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin › Apparence › Application mobile : publicité affichée à l'ouverture de l'app. */
class AppMobileController extends Controller
{
    public function edit(): View
    {
        $r = PubLancement::reglages();

        return view('admin.apparence.application', [
            'r' => $r,
            'imageUrl' => $r['image'] ? Media::url('heros', $r['image']) : null,
            'enLigne' => PubLancement::active() !== null,
            'pubs' => PubApp::orderBy('ordre')->orderByDesc('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'actif' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'supprimer_image' => ['nullable', 'boolean'],
            'lien' => ['nullable', 'url', 'max:500'],
            'duree' => ['required', 'integer', 'between:2,15'],
            'debut' => ['nullable', 'date'],
            'fin' => ['nullable', 'date', 'after_or_equal:debut'],
        ], [], ['image' => 'image', 'lien' => 'lien', 'duree' => 'durée', 'debut' => 'date de début', 'fin' => 'date de fin']);

        $ancienne = ParametreSite::valeur('app.pub.image');
        $valeurs = [
            'app.pub.actif' => $request->boolean('actif') ? '1' : '0',
            'app.pub.lien' => $data['lien'] ?? '',
            'app.pub.duree' => (string) $data['duree'],
            'app.pub.debut' => $data['debut'] ?? '',
            'app.pub.fin' => $data['fin'] ?? '',
        ];

        if ($request->hasFile('image')) {
            $valeurs['app.pub.image'] = Media::store($request->file('image'), 'heros', 'PUBAPP', 1440);
        } elseif ($request->boolean('supprimer_image')) {
            $valeurs['app.pub.image'] = null;
            $valeurs['app.pub.actif'] = '0';
        }

        if ($valeurs['app.pub.actif'] === '1' && ! ($valeurs['app.pub.image'] ?? $ancienne)) {
            return back()->withInput()->withErrors(['image' => 'Ajoutez une image pour activer la publicité.']);
        }

        ParametreSite::enregistrer($valeurs);

        if ($ancienne && array_key_exists('app.pub.image', $valeurs) && $valeurs['app.pub.image'] !== $ancienne) {
            Media::delete('heros', $ancienne);
        }

        return redirect()->route('admin.apparence.application')->with('success', 'Publicité de l\'application mise à jour.');
    }

    /** Ajoute une bannière au carrousel de l'accueil de l'app. */
    public function storePub(Request $request): RedirectResponse
    {
        $data = $this->validerPub($request, true);
        $data['image'] = Media::store($request->file('image'), 'heros', 'PUBBAN', 1600);

        PubApp::create($data);

        return redirect()->route('admin.apparence.application')->with('success', 'Bannière ajoutée au carrousel de l\'application.');
    }

    public function updatePub(Request $request, PubApp $pub): RedirectResponse
    {
        $data = $this->validerPub($request, false);

        if ($request->hasFile('image')) {
            $data['image'] = Media::store($request->file('image'), 'heros', 'PUBBAN', 1600);
        }

        $pub->update($data); // l'ancienne image est supprimée par le modèle

        return redirect()->route('admin.apparence.application')->with('success', 'Bannière mise à jour.');
    }

    public function destroyPub(PubApp $pub): RedirectResponse
    {
        $pub->delete(); // supprime aussi le fichier image

        return redirect()->route('admin.apparence.application')->with('success', 'Bannière supprimée.');
    }

    private function validerPub(Request $request, bool $creation): array
    {
        $data = $request->validate([
            'titre' => ['nullable', 'string', 'max:150'],
            'image' => [$creation ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'lien' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|pratik://)#i'],
            'ordre' => ['nullable', 'integer', 'min:0', 'max:999'],
            'actif' => ['nullable', 'boolean'],
            'debut' => ['nullable', 'date'],
            'fin' => ['nullable', 'date', 'after_or_equal:debut'],
        ], ['lien.regex' => 'Le lien doit commencer par https:// (site web) ou pratik:// (écran de l\'application).'], ['fin' => 'date de fin']);

        unset($data['image']);
        $data['actif'] = $request->boolean('actif');
        $data['ordre'] = (int) ($data['ordre'] ?? 0);

        return $data;
    }
}
