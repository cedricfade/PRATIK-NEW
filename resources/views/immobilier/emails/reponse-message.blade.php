<x-mail::message>
Bonjour {{ $message->prenom }},

{!! nl2br(e($message->reponse)) !!}

— {{ $annonceur }}

<x-mail::panel>
Votre message du {{ $message->created_at?->locale('fr')->isoFormat('D MMMM YYYY') }} :

{{ $message->contactmessage }}
</x-mail::panel>

Message transmis par Pratik Immobilier.
</x-mail::message>
