<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\FactureEmail;
use App\Models\Facture;
use App\Models\Client;

class EmailController extends Controller
{
    /**
     * Envoyer une facture par email
     *
     * @param int $factureId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendFactureEmail($factureId)
    {
        try {
            $facture = Facture::with('client')->findOrFail($factureId);
            $client = $facture->client;

            // Vérifier si le client a une adresse email
            if (!$client->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le client n\'a pas d\'adresse email configurée.'
                ], 400);
            }

            // Vérifier si le PDF existe
            $pdfPath = storage_path('app/factures/' . $facture->id . '.pdf');
            if (!file_exists($pdfPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le PDF de la facture n\'existe pas.'
                ], 400);
            }

            // Envoyer l'email
            Mail::to($client->email)->send(new FactureEmail($facture, $client));

            return response()->json([
                'success' => true,
                'message' => 'Facture envoyée avec succès à ' . $client->email
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()
            ], 500);
        }
    }
}
