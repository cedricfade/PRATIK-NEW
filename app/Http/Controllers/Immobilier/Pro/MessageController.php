<?php

namespace App\Http\Controllers\Immobilier\Pro;

use App\Http\Controllers\Controller;
use App\Mail\Immobilier\ReponseMessageMail;
use App\Models\ContactEntreprise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Boîte de réception de l'annonceur (table contact_entreprises).
 * status : 0 nouveau, 1 lu, 2 répondu.
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $filtre = $request->query('filtre') === 'non-lus' ? 'non-lus' : null;

        // Messages adressés à la société de l'annonceur OU directement à l'un de ses biens.
        $messages = ContactEntreprise::query()
            ->whereIn('entreprise_id', $request->user()->entreprisesSuivies())
            ->when($filtre, fn ($q) => $q->where('status', 0))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('immobilier.pro.messages.index', compact('messages', 'filtre'));
    }

    public function show(Request $request, int $id): View
    {
        $message = $this->messageDe($request, $id);

        if ((int) $message->status === 0) {
            $message->update(['status' => 1]);
        }

        return view('immobilier.pro.messages.show', compact('message'));
    }

    public function repondre(Request $request, int $id): RedirectResponse
    {
        $message = $this->messageDe($request, $id);
        $data = $request->validate(['reponse' => ['required', 'string', 'min:2', 'max:5000']], [], ['reponse' => 'réponse']);

        $message->update(['reponse' => $data['reponse'], 'status' => 2]);

        $user = $request->user()->loadMissing('entreprise:ID,TITRE');

        try {
            Mail::to($message->email)->send(new ReponseMessageMail($message, $user->entreprise?->TITRE ?: $user->name, $message->emailEntreprise ?: $user->email));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Votre réponse est enregistrée mais l\'e-mail n\'a pas pu partir. Contactez la personne par téléphone : '.$message->contact);
        }

        return redirect()->route('immo.pro.messages.index')->with('success', 'Réponse envoyée à '.$message->email.'.');
    }

    private function messageDe(Request $request, int $id): ContactEntreprise
    {
        $message = ContactEntreprise::findOrFail($id);

        abort_unless(in_array((int) $message->entreprise_id, $request->user()->entreprisesSuivies(), true), 403);

        return $message;
    }
}
