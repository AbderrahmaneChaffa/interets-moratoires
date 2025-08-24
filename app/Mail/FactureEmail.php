<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Facture;
use App\Models\Client;

class FactureEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $facture;
    public $client;
    public $emailMessage;
    public $attachments;

    public function __construct(Facture $facture, Client $client, $emailMessage = null, array $attachments = [])
    {
        $this->facture = $facture;
        $this->client = $client;
        $this->emailMessage = $emailMessage;
        $this->attachments = $attachments;
    }

    public function build()
    {
        return $this->view('emails.facture')
            ->subject('Facture N° ' . $this->facture->reference . ' - ' . $this->client->raison_sociale)
            ->with([
                'facture' => $this->facture,
                'client' => $this->client,
                'emailMessage' => $this->emailMessage,
            ]);
    }

    public function attachments(): array
    {
        return $this->attachments; // ici ce sont bien des objets Attachment
    }
}
