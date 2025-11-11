@section('title', 'Créer une facture')

<div class="container mt-4">
    <h2>Créer une facture</h2>
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    <form wire:submit.prevent="store" >
        <div class="mb-3">
            <label>Client <span class="text-danger">*</span></label>
            <select class="form-control" wire:model.defer="client_id" required>
                <option value="">Sélectionner un client</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->raison_sociale }} ({{ $c->nif }})</option>
                @endforeach
            </select>
            @error('client_id')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label>Référence <span class="text-danger">*</span></label>
            <input type="text" wire:model.defer="reference" class="form-control" required>
            @error('reference')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Date facture <span class="text-danger">*</span></label>
            <input type="date" wire:model.defer="date_facture" class="form-control" required>
            @error('date_facture')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Montant HT <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model.defer="montant_ht" class="form-control" required>
            @error('montant_ht')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Date dépôt <span class="text-danger">*</span></label>
            <input type="date" wire:model.defer="date_depot" class="form-control" required>
            @error('date_depot')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Date règlement</label>
            <input type="date" wire:model.defer="date_reglement" class="form-control">
            @error('date_reglement')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label>Net à payer <span class="text-danger">*</span></label>
            <input type="number" step="0.01" wire:model.defer="net_a_payer" class="form-control" required>
            @error('net_a_payer')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        
        <div class="mb-3">
            <label>Statut de la facture <span class="text-danger">*</span></label>
            <select wire:model.defer="statut" class="form-control" required>
                @foreach($statuts as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            @error('statut')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-3">
            <label>Délai légal (en jours) <span class="text-danger">*</span></label>
            <input type="number" wire:model.defer="delai_legal_jours" class="form-control" min="1" max="365" required>
            @error('delai_legal_jours')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" wire:model.defer="calculer_statut_auto" class="form-check-input" id="calculer_statut_auto">
                <label class="form-check-label" for="calculer_statut_auto">
                    Calculer automatiquement le statut et les intérêts (si date_depot et date_reglement sont renseignées)
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label for="facture_pdf">Joindre un PDF</label>
            <input type="file" wire:model="facture_pdf" class="form-control" accept="application/pdf">
            @error('facture_pdf')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Créer la facture</button>
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
