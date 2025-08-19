<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Facture;
use App\Services\InteretService;
use Carbon\Carbon;

class RapportInterets extends Component
{
    public $clientId;
    public $client;
    public $factures;

    public function mount($client)
    {
        $this->clientId = is_numeric($client) ? (int) $client : null;
        $this->client = Client::findOrFail($this->clientId);
    }

    public function render()
    {
        $this->factures = Facture::with('client')
            ->where('client_id', $this->client->id)
            ->orderBy('date_facture')
            ->get()
            ->map(function (Facture $f) {
                // Recalcul de sécurité via service
                $calc = InteretService::calculerInterets($f, (float) ($this->client->taux ?? 0), (string) ($this->client->formule ?? ''));
                $f->jours_retards = $calc['jours'];
                $f->interets_ht = $calc['interet_ht'];
                $f->interets_ttc = $calc['interet_ttc'];
                return $f;
            });

        return view('livewire.rapport-interets', [
            'client' => $this->client,
            'factures' => $this->factures,
            'totaux' => [
                'impayes' => (float) $this->factures->whereNull('date_reglement')->sum('net_a_payer'),
                'interets_ht' => (float) $this->factures->sum('interets_ht'),
                'interets_ttc' => (float) $this->factures->sum('interets_ttc'),
            ],
        ]);
    }
}
