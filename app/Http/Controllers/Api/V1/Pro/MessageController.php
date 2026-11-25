<?php

namespace App\Http\Controllers\Api\V1\Pro;

use App\Http\Controllers\Api\V1\Concerns;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MessageResource;
use App\Mail\Immobilier\ReponseMessageMail;
use App\Models\ContactEntreprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Demandes reçues par l'entreprise de l'annonceur. */
class MessageController extends Controller
{
    use Concerns;

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        // Messages adressés à la société de l'annonceur OU directement à l'un de ses biens.
        $suivies = $request->user()->entreprisesSuivies();

        $messages = ContactEntreprise::query()
            ->whereIn('entreprise_id', $suivies)
            ->when($request->query('statut') === 'non_lus', fn ($q) => $q->where('status', 0))
            ->orderByDesc('id')
            ->paginate($this->parPage($request));

        return MessageResource::collection($messages)->additional(['meta' => [
            'non_lus' => ContactEntreprise::whereIn('entreprise_id', $suivies)->where('status', 0)->count(),
        ]]);
    }

    /** GET /pro/messages/{id} — détail ; le message est marqué comme lu. */
    public function show(Request $request, int $id): MessageResource
    {
        $message = $this->messageDe($request, $id);

        if ((int) $message->status === 0) {
            $message->update(['status' => 1]);
        }

        return new MessageResource($message);
    }

    /** POST /pro/messages/{id}/repondre — enregistre la réponse et l'envoie par e-mail à l'internaute. */
    public function repondre(Request $request, int $id): JsonResponse
    {
        $message = $this->messageDe($request, $id);
        $data = $request->validate(['reponse' => ['required', 'string', 'min:2', 'max:5000']], [], ['reponse' => 'réponse']);

        $message->update(['reponse' => $data['reponse'], 'status' => 2]);

        $user = $request->user()->loadMissing('entreprise:ID,TITRE');
        $envoye = true;

        try {
            Mail::to($message->email)->send(new ReponseMessageMail($message, $user->entreprise?->TITRE ?: $user->name, $message->emailEntreprise ?: $user->email));
        } catch (\Throwable $e) {
            report($e);
            $envoye = false;
        }

        return response()->json([
            'data' => (new MessageResource($message->fresh()))->resolve(),
            'message' => $envoye
                ? 'Réponse envoyée à '.$message->email.'.'
                : 'Réponse enregistrée, mais l\'e-mail n\'a pas pu partir. Contactez la personne par téléphone : '.$message->contact,
            'email_envoye' => $envoye,
        ]);
    }

    private function messageDe(Request $request, int $id): ContactEntreprise
    {
        $message = ContactEntreprise::findOrFail($id);

        abort_unless(in_array((int) $message->entreprise_id, $request->user()->entreprisesSuivies(), true), 403, 'Ce message ne vous est pas adressé.');

        return $message;
    }
}
