<div>
    <div class="modal @if($open) show d-block @else fade @endif" tabindex="-1" role="dialog"
        style="@if($open) background: rgba(0,0,0,0.5); @endif">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Marquer relevé comme payé</h5>
                    <button type="button" class="btn-close" aria-label="Close"
                        wire:click="$set('open', false)"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir marquer ce relevé (<strong>#{{ $releveId }}</strong>) comme payé ?</p>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="markInterets"
                            wire:model="markInteretsAsPaid">
                        <label class="form-check-label" for="markInterets">Marquer aussi tous les intérêts comme
                            payés</label>
                    </div>

                    <div class="mb-2">
                        <label>Commentaire (optionnel)</label>
                        <input type="text" class="form-control" wire:model.defer="commentaire">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="$set('open', false)">Annuler</button>
                    <button class="btn btn-success" wire:click="markAsPaid">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
</div>