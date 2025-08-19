<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;

class GestionInteretsController extends Controller
{
    public function show(Facture $facture)
    {
        return view('factures-interets', compact('facture'));
    }
}
