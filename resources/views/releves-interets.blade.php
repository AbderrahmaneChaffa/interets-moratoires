<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-list"></i> Relevé d'intérêts moratoires
            </h2>
            <a href="{{ route('factures') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>Client:</strong> {{ $releve->client->raison_sociale }}</div>
                        <div class="col-md-4"><strong>Période:</strong> {{ $releve->date_debut->format('d/m/Y') }} - {{ $releve->date_fin->format('d/m/Y') }}</div>
                        <div class="col-md-4"><strong>Statut:</strong> {{ $releve->statut }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Actions</strong>
                    </div>
                    <button class="btn btn-primary" onclick="window.livewire.emit('calculerInteretsReleve')">
                        <i class="fas fa-calculator"></i> Calculer les intérêts du relevé
                    </button>
                </div>
                <div class="card-body">
                    @livewire('releve-interets', ['releveId' => $releve->id], key('releve-'.$releve->id))
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


