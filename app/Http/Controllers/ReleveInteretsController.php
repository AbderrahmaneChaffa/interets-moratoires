<?php

namespace App\Http\Controllers;

use App\Models\Releve;

class ReleveInteretsController extends Controller
{
    public function show(Releve $releve)
    {
        $releve->load(['client', 'factures']);
        return view('releves-interets', compact('releve'));
    }
}


