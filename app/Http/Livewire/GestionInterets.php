<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Facture;
use App\Models\Interet;
use App\Services\InteretService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class GestionInterets extends Component
{
    use WithPagination, WithFileUploads;
    
    public $factureId;
    public $facture = null;
    public $periodesInterets = [];
    public $showCalculModal = false;
    public $periodeSelectionnee = null;
    public $references = [];
    public $pdfUploads = [];

    protected $listeners = ['refreshInterets' => 'refreshData'];

    public function mount($factureId = null)
    {
        if ($factureId) {
            $this->factureId = $factureId;
            $this->loadFacture();
        }
    }

    public function updateReference($interetId)
    {
        $interet = Interet::find($interetId);
        if ($interet && isset($this->references[$interetId])) {
            $interet->update(['reference' => $this->references[$interetId]]);
            session()->flash('message', 'Référence mise à jour avec succès.');
            $this->calculerPeriodes();
        }
    }

    public function uploadPdf($interetId)
    {
        try {
            // Debug: Check if file exists
            if (!isset($this->pdfUploads[$interetId])) {
                session()->flash('error', 'Aucun fichier sélectionné.');
                return;
            }

            $file = $this->pdfUploads[$interetId];
            
            // Debug: Check file properties
            if (!$file) {
                session()->flash('error', 'Fichier invalide.');
                return;
            }

            // Validate file
            $this->validate([
                "pdfUploads.{$interetId}" => 'required|file|mimes:pdf|max:10240', // 10MB max
            ], [
                "pdfUploads.{$interetId}.required" => 'Le fichier PDF est requis.',
                "pdfUploads.{$interetId}.file" => 'Le fichier doit être un fichier valide.',
                "pdfUploads.{$interetId}.mimes" => 'Le fichier doit être un PDF.',
                "pdfUploads.{$interetId}.max" => 'Le fichier ne doit pas dépasser 10MB.',
            ]);

            $interet = Interet::find($interetId);
            if (!$interet) {
                session()->flash('error', 'Intérêt non trouvé.');
                return;
            }

            // Delete old PDF if exists
            if ($interet->pdf_path && Storage::disk('public')->exists($interet->pdf_path)) {
                Storage::disk('public')->delete($interet->pdf_path);
            }

            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists('interets_pdfs')) {
                Storage::disk('public')->makeDirectory('interets_pdfs');
            }

            // Store new PDF with original name
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . $interetId . '_' . $originalName;
            $path = $file->storeAs('interets_pdfs', $filename, 'public');
            
            // Update database
            $interet->update(['pdf_path' => $path]);
            
            // Clear the upload
            $this->pdfUploads[$interetId] = null;
            
            session()->flash('message', 'PDF uploadé avec succès: ' . $originalName);
            $this->calculerPeriodes();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'upload: ' . $e->getMessage());
        }
    }

    public function validerInteret($id)
    {
        $interet = Interet::find($id);
        if ($interet) {
            $interet->update(['valide' => true]);
            session()->flash('message', 'Intérêt validé avec succès.');
        }
    }
    
    public function loadFacture()
    {
        if ($this->factureId) {
            $this->facture = Facture::with(['client', 'interets'])->find($this->factureId);
            $this->calculerPeriodes();
            $this->initializeReferences();
        }
    }

    private function initializeReferences()
    {
        if ($this->facture && $this->facture->interets) {
            foreach ($this->facture->interets as $interet) {
                $this->references[$interet->id] = $interet->reference ?? '';
            }
        }
    }

    public function calculerPeriodes()
    {
        if (!$this->facture) {
            return;
        }

        $this->periodesInterets = InteretService::getInteretsCalcules($this->facture);
        
        // Ensure dates are Carbon objects for proper formatting in Blade
        foreach ($this->periodesInterets as &$periode) {
            if (is_string($periode['date_debut_periode'])) {
                $periode['date_debut_periode'] = Carbon::parse($periode['date_debut_periode']);
            }
            if (is_string($periode['date_fin_periode'])) {
                $periode['date_fin_periode'] = Carbon::parse($periode['date_fin_periode']);
            }
        }
    }

    public function calculerInteret($factureId)
    {
        $facture = Facture::with('client')->findOrFail($factureId);

        if (!$facture->peutGenererInterets()) {
            session()->flash('error', 'Cette facture ne peut pas générer d\'intérêts moratoires.');
            return;
        }

        $interetsCrees = InteretService::calculerEtSauvegarderTousInterets($facture);

        if (empty($interetsCrees)) {
            session()->flash('info', 'Tous les intérêts pour cette facture ont déjà été calculés.');
        } else {
            session()->flash('message', count($interetsCrees) . ' période(s) d\'intérêts calculée(s) et sauvegardée(s).');
        }

        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function calculerInteretPeriode($dateDebut, $dateFin)
    {
        if (!$this->facture) {
            return;
        }

        $dateDebut = Carbon::parse($dateDebut);
        $dateFin = Carbon::parse($dateFin);

        $interet = InteretService::calculerEtSauvegarderInterets($this->facture, $dateDebut, $dateFin);

        if ($interet) {
            session()->flash('message', 'Intérêt calculé et sauvegardé pour cette période.');
        } else {
            session()->flash('info', 'Intérêt déjà calculé pour cette période.');
        }

        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function supprimerInteret($interetId)
    {
        $interet = Interet::findOrFail($interetId);
        $interet->delete();

        session()->flash('message', 'Intérêt supprimé avec succès.');
        $this->loadFacture();
        $this->emit('refreshInterets');
    }

    public function refreshData()
    {
        $this->loadFacture();
    }

    public function render()
    {
        return view('livewire.gestion-interets');
    }
}
