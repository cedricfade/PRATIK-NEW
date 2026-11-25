<?php

namespace App\Mail\Immobilier;

use App\Models\ContactEntreprise;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Réponse de l'annonceur, envoyée à l'internaute depuis l'espace annonceur. */
class ReponseMessageMail extends Mailable
{
    public function __construct(public ContactEntreprise $message, public string $annonceur, public ?string $emailAnnonceur = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->emailAnnonceur ? [new Address($this->emailAnnonceur, $this->annonceur)] : [],
            subject: 'Re : '.$this->message->objet,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'immobilier.emails.reponse-message');
    }
}
