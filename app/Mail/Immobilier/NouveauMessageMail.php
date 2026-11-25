<?php

namespace App\Mail\Immobilier;

use App\Models\ContactEntreprise;
use App\Models\Immobilier;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Prévient l'annonceur qu'un internaute lui a écrit au sujet d'un bien. */
class NouveauMessageMail extends Mailable
{
    public function __construct(public ContactEntreprise $message, public Immobilier $bien)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->message->email, trim($this->message->prenom.' '.$this->message->nom))],
            subject: 'Nouveau message pour votre annonce sur Pratik Immobilier',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'immobilier.emails.nouveau-message');
    }
}
