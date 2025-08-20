<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'parent_id', 'type', 'prestation',
        'reference',
        'date_facture',
        'montant_ht',
        'date_depot',
        'date_reglement',
        'net_a_payer',
        'pdf_path',
        'statut',
        'delai_legal_jours',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_depot' => 'date',
        'date_reglement' => 'date',
        'montant_ht' => 'decimal:2',
        'net_a_payer' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

  

    public function interets()
    {
        return $this->hasMany(Interet::class);
    }

    /**
     * Calcule et met à jour le statut de la facture automatiquement.
     *
     * @return string Le statut calculé
     */
    public function calculerStatut()
    {
        $delai_legal = $this->delai_legal_jours ?? 30;
        
        if (!$this->date_depot) {
            $this->statut = 'En attente';
            return $this->statut;
        }

        $date_limite = $this->date_depot->copy()->addDays($delai_legal);
        $aujourd_hui = now();

        if ($this->date_reglement) {
            // Facture réglée
            if ($this->date_reglement <= $date_limite) {
                $this->statut = 'Payée';
            } else {
                $this->statut = 'Retard de paiement';
            }
        } else {
            // Facture non réglée
            if ($aujourd_hui > $date_limite) {
                $this->statut = 'Impayée';
            } else {
                $this->statut = 'En attente';
            }
        }

        return $this->statut;
    }

    /**
     * Met à jour automatiquement le statut.
     *
     * @return string Le statut calculé
     */
    public function mettreAJourStatut()
    {
        $statut = $this->calculerStatut();
        $this->save();
        return $statut;
    }

    /**
     * Obtient la liste des statuts disponibles.
     *
     * @return array
     */
    public static function getStatutsDisponibles()
    {
        return [
            'Payée' => 'Payée',
            'Impayée' => 'Impayée'
        ];
    }

    /**
     * Formater le montant en DA
     */
    public function getMontantHtFormattedAttribute()
    {
        return number_format($this->montant_ht, 2, ',', ' ') . ' DA';
    }

    public function getNetAPayerFormattedAttribute()
    {
        return number_format($this->net_a_payer, 2, ',', ' ') . ' DA';
    }

    /**
     * Calculer le total des intérêts pour cette facture
     */
    public function getTotalInteretsAttribute()
    {
        return $this->interets()->sum('interet_ttc');
    }

    public function getTotalInteretsFormattedAttribute()
    {
        return number_format($this->total_interets, 2, ',', ' ') . ' DA';
    }

    /**
     * Vérifier si la facture peut générer des intérêts moratoires
     */
    public function peutGenererInterets()
    {
        if (!$this->date_depot) {
            return false;
        }

        $delai_legal = $this->delai_legal_jours ?? 30;
        $date_limite = $this->date_depot->copy()->addDays($delai_legal);
        
        // Si la facture est payée, vérifier si elle a été payée en retard
        if ($this->date_reglement) {
            return $this->date_reglement > $date_limite;
        }
        
        // Si la facture n'est pas payée, vérifier si le délai est dépassé
        return now() > $date_limite;
    }

    /**
     * Obtenir le nombre de jours de retard
     */
    public function getJoursRetardAttribute()
    {
        if (!$this->date_depot) {
            return 0;
        }

        $delai_legal = $this->delai_legal_jours ?? 30;
        $date_limite = $this->date_depot->copy()->addDays($delai_legal);
        $date_ref = $this->date_reglement ?: now();
        return max(0, $date_limite->diffInDays($date_ref, false));

        // return max(0, $date_ref->diffInDays($date_limite, false));
    }

    /**
     * Obtenir le nombre de mois de retard
     */
    public function getMoisRetardAttribute()
    {
        return (int) max(0, ceil($this->jours_retard / 30));
    }
}