<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use App\Mail\Immobilier\NouveauMessageMail;
use App\Models\Contact;
use App\Models\ContactEntreprise;
use App\Models\Immobilier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /** /immobilier/contact */
    public function create(): View
    {
        return view('immobilier.pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('site_web')) {
            return back()->with('success', 'Merci, votre message a bien été envoyé.');
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:100'],
            'objet' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:3000'],
        ], [], ['telephone' => 'téléphone']);

        Contact::create([
            'nom' => $data['nom'],
            'contact' => $data['telephone'],
            'email' => $data['email'],
            'objet' => $data['objet'],
            'message' => $data['message'],
            'categorie' => 'Immobilier',
            'date' => now()->toDateString(),
            'heure' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Merci, votre message a bien été envoyé. Nous vous répondons rapidement.');
    }

    /** Message d'un internaute à l'annonceur d'un bien (fiche /immobilier/proprietes-{lien}). */
    public function annonceur(Request $request, string $lien): RedirectResponse
    {
        $bien = Immobilier::query()
            ->select('immobilier.*')
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->where('entreprises.LIEN', $lien)
            ->publie()
            ->with(['entreprise:ID,TITRE,LIEN', 'auteur:id,email,entreprise_id', 'auteur.entreprise.annuaire:ID,ENTREPRISE_ID,EMAIL_1,EMAIL_2'])
            ->firstOrFail();

        $retour = redirect()->to(route('immo.bien', $lien).'#contact');

        if ($request->filled('site_web')) {
            return $retour->with('success', 'Votre message a été transmis à l\'annonceur.');
        }

        $data = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:30', 'regex:/^[0-9 +().-]{8,30}$/'],
            'email' => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], ['telephone.regex' => 'Le numéro de téléphone n\'est pas valide.'], ['prenom' => 'prénom', 'telephone' => 'téléphone']);

        $auteur = $bien->auteur;
        $emailAnnonceur = collect($auteur?->entreprise?->annuaire?->emails ?? [])->push($auteur?->email)
            ->first(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        $message = ContactEntreprise::create([
            'entreprise_id' => $auteur?->entreprise_id ?: $bien->ENTREPRISE_ID,
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'contact' => $data['telephone'],
            'email' => $data['email'],
            'objet' => 'Votre annonce : '.$bien->entreprise->TITRE,
            'contactmessage' => $data['message'],
            'emailEntreprise' => $emailAnnonceur,
            'status' => 0,
        ]);

        if ($emailAnnonceur) {
            // Envoyé après la réponse : le visiteur n'attend pas le serveur d'e-mail.
            $mail = new NouveauMessageMail($message, $bien);
            dispatch(function () use ($emailAnnonceur, $mail) {
                try {
                    Mail::to($emailAnnonceur)->send($mail);
                } catch (\Throwable $e) {
                    report($e); // le message reste consultable dans l'espace annonceur
                }
            })->afterResponse();
        }

        return $retour->with('success', 'Votre message a été transmis à l\'annonceur. Il vous répondra par e-mail ou par téléphone.');
    }
}
