<?php

namespace App\Http\Controllers;

use App\Models\Releve;

class ReleveInteretsController extends Controller
{
    public function show(Releve $releve)
    {
        return view('releves-interets', compact('releve'));
    }
}


