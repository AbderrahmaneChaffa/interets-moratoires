<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacturePdfController extends Controller
{
    public function upload(Request $request, Facture $facture)
    {
        $request->validate([
            'facture_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $pdfPath = $request->file('facture_pdf')->store('factures','public');

        // Supprimer l'ancien fichier si existant
        if ($facture->pdf_path) {
            Storage::delete($facture->pdf_path);
        }

        $facture->update([
            'pdf_path' => $pdfPath,
        ]);

        return redirect()->back()->with('message', 'PDF ajouté avec succès.');
    }
}
