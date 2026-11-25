<?php

namespace App\Http\Controllers\Api\V1\Pro;

use App\Http\Controllers\Api\V1\Concerns;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MessageResource;
use App\Models\ContactEntreprise;
use Illuminate\Http\Request;
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
            ->orderByDesc('id')
            ->paginate($this->parPage($request));

        return MessageResource::collection($messages)->additional(['meta' => [
            'non_lus' => ContactEntreprise::whereIn('entreprise_id', $suivies)->where('status', 0)->count(),
        ]]);
    }
}
