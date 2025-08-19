<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Facture;
use App\Models\Client;

class FactureEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $facture;
    public $client;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Facture $facture, Client $client)
    {
        $this->facture = $facture;
        $this->client = $client;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: 'interet.moratoire@hts-hightechsystems.com',
            subject: 'Facture N° ' . $this->facture->numero . ' - ' . $this->client->raison_sociale,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.facture',
            with: [
                'facture' => $this->facture,
                'client' => $this->client,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];
        
        // Ajouter le PDF de la facture s'il existe
        // Si la facture a un chemin PDF stocké via storage public
        if (!empty($this->facture->pdf_path)) {
            $publicPath = storage_path('app/public/' . ltrim($this->facture->pdf_path, '/'));
            if (file_exists($publicPath)) {
                $attachments[] = Attachment::fromPath($publicPath)
                    ->as('Facture_' . $this->facture->reference . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        // Fallback legacy path si existait
        $legacyPath = storage_path('app/factures/' . $this->facture->id . '.pdf');
        if (file_exists($legacyPath)) {
            $attachments[] = Attachment::fromPath($legacyPath)
                ->as('Facture_' . $this->facture->numero . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
