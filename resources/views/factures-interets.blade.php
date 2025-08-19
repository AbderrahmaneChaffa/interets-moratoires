<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-calculator"></i> Gestion des intérêts moratoires
            </h2>
            <a href="{{ route('factures') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux factures
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('gestion-interets', ['factureId' => $facture->id])
        </div>
    </div>
</x-app-layout>
