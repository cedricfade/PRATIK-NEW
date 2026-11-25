<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\Immobilier\NouveauMessageMail;
use App\Models\Annuaire;
use App\Models\ContactEntreprise;
use App\Models\Entreprise;
use App\Models\Immobilier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Messages envoyés depuis l'application (mêmes règles que les formulaires du site) :
 *  - POST /immobilier/biens/{lien}/contact     → annonceur du bien
 *  - POST /annuaire/entreprises/{lien}/contact → entreprise de l'annuaire
 * Le message est enregistré (espace annonceur / boîte de l'entreprise) puis envoyé par e-mail.
 */
class ContactController extends Controller
{
    public function bien(Request $request, string $lien): JsonResponse
    {
        $bien = Immobilier::query()
            ->select('immobilier.*')
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->where('entreprises.LIEN', $lien)
            ->publie()
            ->with(['entreprise:ID,TITRE,LIEN', 'auteur:id,email,entreprise_id', 'auteur.entreprise.annuaire:ID,ENTREPRISE_ID,EMAIL_1,EMAIL_2'])
            ->firstOrFail();

        $data = $this->valider($request, false);
        $auteur = $bien->auteur;
        $email = collect($auteur?->entreprise?->annuaire?->emails ?? [])->push($auteur?->email)
            ->first(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        $message = ContactEntreprise::create([
            'entreprise_id' => $auteur?->entreprise_id ?: $bien->ENTREPRISE_ID,
            'prenom' => $data['prenom'] ?? '',
            'nom' => $data['nom'],
            'contact' => $data['telephone'],
            'email' => $data['email'],
            'objet' => 'Votre annonce : '.$bien->entreprise->TITRE,
            'contactmessage' => $data['message'],
            'emailEntreprise' => $email,
            'status' => 0,
        ]);

        if ($email) {
            // Envoyé après la réponse : le visiteur n'attend pas le serveur d'e-mail.
            $mail = new NouveauMessageMail($message, $bien);
            dispatch(function () use ($email, $mail) {
                try {
                    Mail::to($email)->send($mail);
                } catch (\Throwable $e) {
                    report($e); // le message reste consultable dans l'espace annonceur
                }
            })->afterResponse();
        }

        return response()->json(['message' => 'Votre message a été transmis à l\'annonceur. Il vous répondra par e-mail ou par téléphone.'], 201);
    }

    public function entreprise(Request $request, string $lien): JsonResponse
    {
        $entreprise = Entreprise::where('LIEN', $lien)->whereHas('annuaire')->firstOrFail(['ID', 'TITRE', 'LIEN']);
        $data = $this->valider($request, true);

        $annuaire = Annuaire::where('ENTREPRISE_ID', $entreprise->ID)->first(['ID', 'EMAIL_1', 'EMAIL_2']);
        $email = $annuaire?->emails[0] ?? null;
        $objet = $data['objet'] ?? 'Demande d\'information via l\'application Pratik';

        ContactEntreprise::create([
            'entreprise_id' => $entreprise->ID,
            'prenom' => $data['prenom'] ?? '',
            'nom' => $data['nom'],
            'contact' => $data['telephone'],
            'email' => $data['email'],
            'objet' => $objet,
            'contactmessage' => $data['message'],
            'emailEntreprise' => $email,
            'status' => 0,
        ]);

        if ($email) {
            // Envoyé après la réponse : le visiteur n'attend pas le serveur d'e-mail.
            dispatch(function () use ($email, $data, $entreprise, $objet) {
                try {
                    Mail::raw(
                    "Bonjour,\n\nVous avez reçu un message via votre fiche Pratik ({$entreprise->TITRE}), depuis l'application mobile.\n\n"
                    ."De : {$data['nom']} ".($data['prenom'] ?? '')."\nTéléphone : {$data['telephone']}\nE-mail : {$data['email']}\n"
                    ."Objet : {$objet}\n\n{$data['message']}\n\n— Pratik, l'annuaire pratique de Côte d'Ivoire",
                    fn ($m) => $m->to($email)->replyTo($data['email'], $data['nom'])->subject('Pratik — '.$objet),

                    );
                } catch (\Throwable $e) {
                    Log::warning('Envoi du message entreprise impossible : '.$e->getMessage());
                }
            })->afterResponse();
        }

        return response()->json(['message' => 'Votre message a bien été transmis à '.$entreprise->TITRE.'.'], 201);
    }

    private function valider(Request $request, bool $avecObjet): array
    {
        return $request->validate([
            'prenom' => ['nullable', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:30', 'regex:/^[0-9 +().-]{8,30}$/'],
            'email' => ['required', 'email', 'max:150'],
            'objet' => [$avecObjet ? 'nullable' : 'prohibited', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], ['telephone.regex' => 'Le numéro de téléphone n\'est pas valide.'], ['prenom' => 'prénom', 'telephone' => 'téléphone']);
    }
}
