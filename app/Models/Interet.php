<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Interet extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'reference',
        'date_debut_periode',
        'date_fin_periode',
        'statut',
        'jours_retard',
        'interet_ht',
        'interet_ttc',
        'pdf_path',
        'valide',

    ];

    protected $casts = [
        'date_debut_periode' => 'date',
        'date_fin_periode' => 'date',
        'interet_ht' => 'decimal:2',
        'interet_ttc' => 'decimal:2'
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Formater le montant en DA
     */
    public function getInteretHtFormattedAttribute()
    {
        return number_format($this->interet_ht, 2, ',', ' ') . ' DA';
    }

    public function getInteretTtcFormattedAttribute()
    {
        return number_format($this->interet_ttc, 2, ',', ' ') . ' DA';
    }

    /**
     * Vérifier si un intérêt existe déjà pour une période donnée
     */
    public static function existsForPeriod($factureId, $dateDebut, $dateFin)
    {
        return self::where('facture_id', $factureId)
            ->where('date_debut_periode', $dateDebut)
            ->where('date_fin_periode', $dateFin)
            ->exists();
    }
}