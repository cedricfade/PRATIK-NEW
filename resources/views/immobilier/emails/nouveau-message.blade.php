<x-mail::message>
# Nouveau message

**{{ $message->prenom }} {{ $message->nom }}** vous a écrit au sujet de votre annonce
« {{ $bien->entreprise->TITRE }} ».

<x-mail::panel>
{{ $message->contactmessage }}
</x-mail::panel>

- Téléphone : {{ $message->contact }}
- E-mail : {{ $message->email }}

Vous pouvez répondre directement à cet e-mail, ou depuis votre espace annonceur.

<x-mail::button :url="route('immo.pro.messages.show', $message->id)">
Ouvrir dans mon espace
</x-mail::button>

L'équipe Pratik Immobilier
</x-mail::message>
