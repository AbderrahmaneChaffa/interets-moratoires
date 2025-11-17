<?php

namespace App\Http\Livewire;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Models\Releve;
use App\Models\Interet;
use App\Models\Facture;
use Illuminate\Support\Facades\DB;

class ReleveInterets extends Component
{
    public $releveId;
    public $releve;
    public $releveStatus;
    public $releveAmount;
    public $periodes = [];

    // Modals
    public $showPayModal = false;
    public $payInteretId = null;
    public $showValidateAllModal = false;
    public $showRelevePayModal = false;
    public $markInteretsAsPaid = false;

    // Factures modals
    public $showFacturePayModal = false;
    public $showInvoiceFacturePayModal = false;
    public $payFactureId = null;
    public $showFacturesPayAllModal = false;
    // Totaux
    public $totalImpayes = 0;
    public $totalImpayesHT = 0;
    public $totalImpayesTTC = 0;

    public $lastInvoicePath = null;
    protected $listeners = [
        'calculerInteretsReleve' => 'calculer',
        'openRelevePayModal' => 'openRelevePayModal',
        'openFacturePayModal' => 'openFacturePayModal',
        'openFacturesPayAllModal' => 'openFacturesPayAllModal',
        'marquerFactureImpaye' => 'marquerFactureImpaye',
        'refreshPeriodes' => 'loadPeriodes',
    ];

    public function mount($releveId)
    {
        $this->releveId = $releveId;
        $this->loadData();
        $this->loadPeriodes();
    }

    public function loadData()
    {
        $this->releve = Releve::with(['client'])->findOrFail($this->releveId);
        $this->refreshPeriodes();

    }
    public function loadPeriodes()
    {
        // si tu construis $periodes ailleurs, adapte :
        // $this->periodes = $this->fetchPeriodesFromDbOrCompute();
        // Exemple placeholder : (ne pas oublier de remplacer par ta logique)
        // $this->periodes = session('periodes') ?: [];

        // recalculer totaux et info releve
        $this->computeTotals();
        $this->loadLastInvoice();
    }
    public function refreshPeriodes()
    {
        $interets = Interet::where('releve_id', $this->releveId)
            ->orderBy('date_debut_periode')
            ->get();

        $this->periodes = [];
        $mois = 1;
        foreach ($interets as $i) {
            $this->periodes[] = [
                'mois' => $mois++,
                'date_debut' => $i->date_debut_periode,
                'date_fin' => $i->date_fin_periode,
                'jours_retard' => $i->jours_retard,
                'interet_ht' => $i->interet_ht,
                'interet_ttc' => $i->interet_ttc,
                'reference' => $i->reference,
                'pdf_path' => $i->pdf_path,
                'statut' => $i->statut,
                'valide' => $i->valide,
                'id' => $i->id,
            ];
        }
    }
    protected function computeTotals()
    {
        $impayes = collect($this->periodes)->filter(function ($p) {
            return ($p['statut'] ?? 'Impayé') !== 'Payé';
        });

        $this->totalImpayesHT = $impayes->sum(fn($p) => floatval($p['interet_ht'] ?? 0));
        $this->totalImpayesTTC = $impayes->sum(fn($p) => floatval($p['interet_ttc'] ?? 0));
        $this->totalImpayes = $this->totalImpayesTTC; // ou HT selon besoin

        // releve status / amount (adaptation selon ta structure)
        $this->releveStatus = $this->releve->statut ?? ($this->totalImpayes == 0 ? 'Payé' : 'Impayé');
        $this->releveAmount = $this->releve->montant_total_ht ?? null;
    }
    protected function loadLastInvoice()
    {
        $last = Invoice::where('releve_id', $this->releveId)->latest('created_at')->first();
        $this->lastInvoicePath = $last?->path;
    }
    // Générer facture pour une période donnée
    public function generateInvoice($periodeId)
    {
        $periode = collect($this->periodes)->firstWhere('id', $periodeId);
        if (!$periode) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Période introuvable']);
            return;
        }

        $periodes = [$periode];
        $this->createAndStoreInvoice($periodes, "facture_interet_{$periodeId}");
        $this->loadPeriodes();
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Facture générée']);
    }

    // Générer facture qui regroupe tous les intérêts impayés
    public function generateInvoiceUnpaid()
    {
        $impayes = collect($this->periodes)->filter(fn($p) => ($p['statut'] ?? 'Impayé') !== 'Payé');
        if ($impayes->isEmpty()) {
            $this->dispatchBrowserEvent('notify', ['type' => 'info', 'message' => 'Aucun intérêt impayé à facturer']);
            return;
        }

        $this->createAndStoreInvoice($impayes->values()->all(), "facture_interets_impayes_releve_{$this->releveId}");
        $this->loadPeriodes();
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Facture globale générée']);
    }

    protected function createAndStoreInvoice(array $periodes, string $filenamePrefix)
    {
        // préparation données pour le PDF
        $totalHT = collect($periodes)->sum(fn($p) => floatval($p['interet_ht'] ?? 0));
        $totalTTC = collect($periodes)->sum(fn($p) => floatval($p['interet_ttc'] ?? 0));

        $data = [
            'periodes' => $periodes,
            'totalHT' => $totalHT,
            'totalTTC' => $totalTTC,
            'releve' => $this->releve,
            'date' => now()->format('d/m/Y'),
        ];

        // // render view invoice -> pdf
        // $pdf = Pdf::loadView('invoices.interets', $data);
        // $fileName = $filenamePrefix . '_' . now()->format('Ymd_His') . '.pdf';
        // $path = "invoices/releves/{$this->releveId}/{$fileName}";
        $pdf = Pdf::loadView('invoices.interets', $data);

        // Storage::put($path, $pdf->output());
        $fileName = $filenamePrefix . '_' . now()->format('Ymd_His') . '.pdf';
        // Utiliser le disque "public" (accessible via storage:link)
        $path = "invoices/releves/{$this->releveId}/{$fileName}";

        // Écrire le fichier et définir la visibilité publique
        Storage::disk('public')->put($path, $pdf->output());

        // URL publique (ex: /storage/invoices/releves/123/...)
        $url = Storage::disk('public')->url($path);
        // créer une entrée invoice dans la table invoices
        $invoice = Invoice::create([
            'releve_id' => $this->releveId,
            'path' => $path,
            'amount_ht' => $totalHT,
            'amount_ttc' => $totalTTC,
            'status' => 'Impayé',
            'meta' => ['periodes' => collect($periodes)->pluck('id')->all()],
        ]);

        // Option facultative : lier le pdf_path sur chaque période (si tu veux)
        foreach ($periodes as $p) {
            // si tu as un model Period, mets à jour la colonne pdf_path
            // Period::where('id', $p['id'])->update(['pdf_path' => $path]);
            // sinon, si tu travailles uniquement en mémoire, ignore
        }

        // garder pour affichage
        $this->lastInvoicePath = $path;
    }

    // télécharger facture (si besoin)
    public function downloadInvoice($invoiceId)
    {
        $inv = Invoice::find($invoiceId);
        if (!$inv || !Storage::exists($inv->path)) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Facture introuvable']);
            return;
        }

        // redirection directe pour télécharger (Laravel - return response)
        return response()->streamDownload(function () use ($inv) {
            echo Storage::get($inv->path);
        }, basename($inv->path));
    }
    // marquer facture payée (utilisé par ton modal)
    public function marquerInvoiceFacturePaye($invoiceId)
    {
        $inv = Invoice::find($invoiceId);
        if (!$inv) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Facture introuvable']);
            return;
        }

        $inv->update([
            'status' => 'Payé',
            'paid_at' => now(),
        ]);

        // option : marquer les periodes liées comme payées dans ta logique métier
        $periodesIds = $inv->meta['periodes'] ?? [];
        foreach ($periodesIds as $pid) {
            // Period::where('id', $pid)->update(['statut' => 'Payé']);
        }

        $this->closeModals();
        $this->loadPeriodes();
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Facture marquée comme payée']);
    }

    public function openInvoiceFacturePayModal($invoiceId)
    {
        $inv = Invoice::find($invoiceId);
        if (!$inv)
            return;
        $this->selectedInvoiceId = $inv->id;
        $this->selectedInvoiceAmount = $inv->amount_ttc;
        $this->showInvoiceFacturePayModal = true;
    }

    public function calculer()
    {
        $this->releve->calculerInterets();
        $this->releve->refresh();
        $this->refreshPeriodes();
        $this->dispatchBrowserEvent('notification', ['message' => 'Intérêts du relevé calculés.']);
    }

    public function validerInteret($interetId)
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($interetId);
            $oldValide = $interet->valide;
            $interet->update(['valide' => true]);

            // Log audit
            $interet->logChange(
                'validated',
                'valide',
                $oldValide,
                true,
                'Intérêt validé'
            );

            DB::commit();
            $this->refreshPeriodes();
            session()->flash('message', 'Intérêt validé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    public function supprimerInteret($interetId)
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($interetId);

            // Log audit before deletion
            $interet->logChange(
                'deleted',
                null,
                null,
                null,
                'Intérêt supprimé',
                ['interet_data' => $interet->toArray()]
            );

            $interet->delete();
            DB::commit();
            $this->refreshPeriodes();
            session()->flash('message', 'Intérêt supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function openRelevePayModal()
    {
        $this->showRelevePayModal = true;
        $this->markInteretsAsPaid = false;
    }

    public function marquerRelevePaye()
    {
        DB::beginTransaction();
        try {
            $oldStatut = $this->releve->statut;
            $this->releve->update(['statut' => 'Payé']);

            // Log audit
            $this->releve->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Payé',
                'Relevé marqué comme payé'
            );

            // Si l'utilisateur veut aussi marquer les intérêts comme payés
            if ($this->markInteretsAsPaid) {
                $count = Interet::where('releve_id', $this->releveId)
                    ->where('statut', '!=', 'Payé')
                    ->update(['statut' => 'Payé']);

                // Log audit pour les intérêts
                Interet::logAudit(
                    'bulk_status_changed',
                    null,
                    'statut',
                    'Impayé',
                    'Payé',
                    sprintf('%d intérêt(s) marqué(s) comme payé(s) avec le relevé', $count),
                    ['releve_id' => $this->releveId, 'count' => $count]
                );
            }

            DB::commit();
            $this->releve->refresh();
            $this->refreshPeriodes();
            $this->showRelevePayModal = false;
            session()->flash('message', 'Relevé marqué comme payé' . ($this->markInteretsAsPaid ? ' avec tous les intérêts associés' : '') . '.');

            // <-- Ajout : demander au navigateur d'actualiser la page
            $this->dispatchBrowserEvent('releve:reload');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }


    public function marquerReleveImpaye()
    {
        DB::beginTransaction();
        try {
            $oldStatut = $this->releve->statut;
            $this->releve->update(['statut' => 'Impayé']);

            // Log audit
            $this->releve->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Impayé',
                'Relevé marqué comme impayé'
            );

            DB::commit();
            $this->releve->refresh();
            session()->flash('message', 'Relevé marqué comme impayé.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openPayModal($interetId)
    {
        $this->payInteretId = $interetId;
        $this->showPayModal = true;
    }

    public function marquerInteretPaye()
    {
        DB::beginTransaction();
        try {
            $interet = Interet::findOrFail($this->payInteretId);

            if ($interet->statut === 'Payé') {
                session()->flash('info', 'Cet intérêt est déjà marqué comme payé.');
                $this->showPayModal = false;
                return;
            }

            $oldStatut = $interet->statut;
            $interet->update(['statut' => 'Payé']);

            // Log audit
            $interet->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Payé',
                'Intérêt marqué comme payé'
            );

            DB::commit();
            $this->refreshPeriodes();
            $this->showPayModal = false;
            session()->flash('message', 'Intérêt marqué comme payé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openValidateAllModal()
    {
        $this->showValidateAllModal = true;
    }

    public function validerTousInterets()
    {
        DB::beginTransaction();
        try {
            $interets = Interet::where('releve_id', $this->releveId)
                ->where('valide', false)
                ->get();

            $count = 0;
            foreach ($interets as $interet) {
                $interet->update(['valide' => true]);
                $count++;

                // Log audit
                $interet->logChange(
                    'validated',
                    'valide',
                    false,
                    true,
                    'Intérêt validé'
                );
            }

            DB::commit();
            $this->refreshPeriodes();
            $this->showValidateAllModal = false;
            session()->flash('message', sprintf('%d intérêt(s) validé(s) avec succès.', $count));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    public function closeModals()
    {
        $this->showInvoiceFacturePayModal = false;
        $this->selectedInvoiceId = null;
        $this->selectedInvoiceAmount = 0;
        $this->showPayModal = false;
        $this->showValidateAllModal = false;
        $this->showRelevePayModal = false;
        $this->payInteretId = null;
        $this->markInteretsAsPaid = false;
        $this->showFacturePayModal = false;
        $this->showFacturesPayAllModal = false;
        $this->payFactureId = null;
    }

    // ========== GESTION DES FACTURES ==========

    public function openFacturePayModal($factureId)
    {
        $this->payFactureId = $factureId;
        $this->showFacturePayModal = true;
    }

    public function marquerFacturePaye()
    {
        DB::beginTransaction();
        try {
            $facture = Facture::findOrFail($this->payFactureId);

            if ($facture->statut === 'Payé') {
                session()->flash('info', 'Cette facture est déjà marquée comme payée.');
                $this->showFacturePayModal = false;
                return;
            }

            $oldStatut = $facture->statut;

            // Si on marque comme payé, mettre aussi la date de règlement à aujourd'hui si elle n'existe pas
            if (!$facture->date_reglement) {
                $facture->update([
                    'statut' => 'Payé',
                    'date_reglement' => now(),
                ]);
            } else {
                $facture->update(['statut' => 'Payé']);
            }

            // Log audit
            $facture->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Payé',
                'Facture marquée comme payée'
            );

            // Mettre à jour le statut du relevé si nécessaire
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            $this->showFacturePayModal = false;
            session()->flash('message', 'Facture marquée comme payée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function marquerFactureImpaye($factureId)
    {
        DB::beginTransaction();
        try {
            $facture = Facture::findOrFail($factureId);

            if ($facture->statut === 'Impayé') {
                session()->flash('info', 'Cette facture est déjà marquée comme impayée.');
                return;
            }

            $oldStatut = $facture->statut;
            $facture->update([
                'statut' => 'Impayé',
                'date_reglement' => null, // Retirer la date de règlement si on marque comme impayé
            ]);

            // Log audit
            $facture->logChange(
                'status_changed',
                'statut',
                $oldStatut,
                'Impayé',
                'Facture marquée comme impayée'
            );

            // Mettre à jour le statut du relevé si nécessaire
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            session()->flash('message', 'Facture marquée comme impayée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function openFacturesPayAllModal()
    {
        $this->showFacturesPayAllModal = true;
    }

    public function marquerToutesFacturesPayees()
    {
        DB::beginTransaction();
        try {
            $factures = Facture::where('releve_id', $this->releveId)
                ->where('statut', '!=', 'Payé')
                ->get();

            $count = 0;
            foreach ($factures as $facture) {
                $oldStatut = $facture->statut;

                // Mettre la date de règlement si elle n'existe pas
                if (!$facture->date_reglement) {
                    $facture->update([
                        'statut' => 'Payé',
                        'date_reglement' => now(),
                    ]);
                } else {
                    $facture->update(['statut' => 'Payé']);
                }

                $count++;

                // Log audit
                $facture->logChange(
                    'status_changed',
                    'statut',
                    $oldStatut,
                    'Payé',
                    'Facture marquée comme payée (toutes les factures)'
                );
            }

            // Mettre à jour le statut du relevé
            $this->releve->refresh();
            $this->releve->calculerStatut();
            $this->releve->save();

            // Log audit pour le relevé
            $this->releve->logChange(
                'status_changed',
                'statut',
                $this->releve->getOriginal('statut'),
                'Payé',
                sprintf('Toutes les factures (%d) marquées comme payées', $count)
            );

            DB::commit();
            $this->releve->refresh();
            $this->loadData();
            $this->showFacturesPayAllModal = false;
            session()->flash('message', sprintf('%d facture(s) marquée(s) comme payée(s) avec succès.', $count));
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.releve-interets');
    }
}


