<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ProtegeContreSpam;
use App\Models\Commune;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** "Se faire connaître" (inscription d'entreprise) et formulaire de contact → table contacts. */
class FormulaireController extends Controller
{
    use ProtegeContreSpam;

    public function souscription(): View
    {
        $communes = Cache::remember('communes:liste', 86400, fn () => Commune::where('DESIGNATION', '!=', '')->orderBy('DESIGNATION')->get(['ID', 'DESIGNATION', 'LIEN']));

        return view('site.souscription', compact('communes'));
    }

    public function enregistrerSouscription(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entreprise' => ['required', 'string', 'max:200'],
            'activite' => ['required', 'string', 'max:300'],
            'commune' => ['nullable', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'formule' => ['nullable', 'in:gratuite,pro'],
            'message' => ['nullable', 'string', 'max:3000'],
        ], [], ['entreprise' => "nom de l'entreprise", 'activite' => 'activité', 'telephone' => 'téléphone']);

        $retour = redirect()->route('souscription')->with('success', 'Merci ! Votre demande est enregistrée, un conseiller Pratik vous rappelle sous 48 h ouvrées.');

        if ($this->estUnRobot($request)) {
            return $retour;
        }

        $message = "Entreprise : {$data['entreprise']}\nActivité : {$data['activite']}\nCommune : ".($data['commune'] ?: '—')
            ."\nFormule souhaitée : ".(($data['formule'] ?? '') === 'pro' ? 'Pro (mise en avant)' : 'Fiche gratuite')
            .(filled($data['message'] ?? null) ? "\n\n".$data['message'] : '');

        $this->enregistrer([
            'nom' => $data['nom'],
            'contact' => $data['telephone'],
            'email' => $data['email'],
            'objet' => 'Inscription : '.$data['entreprise'],
            'message' => $message,
            'categorie' => 'Souscription',
        ]);

        return $retour;
    }

    public function contact(): View
    {
        return view('site.contact');
    }

    public function enregistrerContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'objet' => ['required', 'string', 'max:250'],
            'message' => ['required', 'string', 'max:3000'],
        ], [], ['telephone' => 'téléphone']);

        $retour = redirect()->route('contact')->with('success', 'Message envoyé. Nous vous répondons dès que possible.');

        if ($this->estUnRobot($request)) {
            return $retour;
        }

        $this->enregistrer([
            'nom' => $data['nom'],
            'contact' => $data['telephone'] ?? null,
            'email' => $data['email'],
            'objet' => $data['objet'],
            'message' => $data['message'],
            'categorie' => 'Contact',
        ]);

        return $retour;
    }

    private function enregistrer(array $champs): void
    {
        Contact::create($champs + ['date' => now()->toDateString(), 'heure' => now()->format('H:i:s')]);

        try {
            Mail::raw(
                "Nouveau message ({$champs['categorie']}) sur pratik-ci.com\n\nDe : {$champs['nom']}\nTéléphone : ".($champs['contact'] ?: '—')
                ."\nE-mail : {$champs['email']}\nObjet : {$champs['objet']}\n\n{$champs['message']}",
                fn ($m) => $m->to(config('pratik.contact.email'))->replyTo($champs['email'], $champs['nom'])->subject('[Pratik] '.$champs['objet']),
            );
        } catch (\Throwable $e) {
            Log::warning('Notification e-mail du formulaire impossible : '.$e->getMessage());
        }
    }
}
