<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Releve extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'client_id',
        'reference',
        'date_debut',
        'date_fin',
        'date_creation',
        'statut',
        'categorie',
        'montant_total_ht',
        'date_derniere_facture',
        'releve_pdf',

    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_creation' => 'date',
        'date_derniere_facture' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function interets()
    {
        return $this->hasMany(Interet::class);
    }

    public function calculerStatut(): string
    {
        $toutesPayees = $this->factures()->where('statut', '!=', 'Payée')->count() === 0;
        $this->statut = $toutesPayees ? 'Payé' : 'Impayé';
        return $this->statut;
    }

    public function mettreAJourStatut(): string
    {
        $statut = $this->calculerStatut();
        $this->save();
        return $statut;
    }

    /**
     * Calcule les intérêts du relevé en agrégeant les factures liées
     * et en appliquant les règles métier sur la période du relevé.
     * Crée des enregistrements Interet liés au relevé.
     */
    public function calculerInterets(): array
    {
        $this->loadMissing(['client', 'factures']);

        // Montant de référence = somme des montants HT impayés dans le relevé
        $montantReference = (float) $this->factures
            ->filter(fn($f) => $f->statut !== 'Payée')
            ->sum('montant_ht');

        // if ($montantReference <= 0) {
        //     return [];
        // }
        // si pas de factures, on prend le montant total du relevé comme base
        if ($montantReference <= 0) {
            $montantReference = (float) $this->montant_total_ht ?? 0;
        }
        // Si toujours rien, on sort
        if ($montantReference <= 0) {
            return [];
        }
        // Déterminer la date de départ = date_derniere_facture + 30 jours
        $dateBase = $this->date_derniere_facture ?? $this->factures->max('date_facture');
        if (!$dateBase) {
            return [];
        }

        $dateBase = $dateBase instanceof \Carbon\Carbon ? $dateBase : \Carbon\Carbon::parse($dateBase);
        $dateDebutGrace = $dateBase->copy()->addDays(30);

        // Déterminer la date de fin du calcul
        $estPaye = $this->statut === 'Payé'
            || $this->factures->every(fn($f) => $f->statut === 'Payée');

        if ($estPaye) {
            $dateFin = $this->factures->max('date_paiement')
                ? \Carbon\Carbon::parse($this->factures->max('date_paiement'))
                : now();
        } else {
            $dateFin = now();
        }

        if ($dateFin <= $dateDebutGrace) {
            return [];
        }

        // Construction des périodes mensuelles (uniquement mois complets)
        $periodes = [];
        $mois = 1;
        $curseur = $dateDebutGrace->copy();
        while ($curseur < $dateFin) {
            $debut = $curseur->copy();
            $fin = $debut->copy()->addMonth();
            
            // Ne créer une période que si c'est un mois complet (fin <= dateFin)
            // Si la période est incomplète, on l'ignore pour éviter les doublons
            if ($fin <= $dateFin) {
                $periodes[] = [
                    'mois' => $mois,
                    'date_debut_periode' => $debut,
                    'date_fin_periode' => $fin,
                ];
                $mois++;
            }
            $curseur = $fin->copy();
        }

        // Application des règles métier
        $clientName = strtoupper($this->client->raison_sociale);
        $interetsCrees = [];

        foreach ($periodes as $periode) {
            $joursRetard = $periode['date_fin_periode']->diffInDays($periode['date_debut_periode']);

            $interet_ht = 0.0;
            switch (true) {
                case str_contains($clientName, 'ALGERIE POSTE'):
                    $interet_ht = ($montantReference * $joursRetard * 0.09) / 360.0;
                    break;
                case str_contains($clientName, 'CPA'):
                    $interet_ht = $montantReference * 0.05; // par mois
                    break;
                case str_contains($clientName, 'BNA'):
                case str_contains($clientName, 'BDL'):
                case str_contains($clientName, 'CNEP'):
                    $interet_ht = $montantReference * 0.10; // par mois
                    break;
                default:
                    $taux = (float) ($this->client->taux ?? 0.1);
                    $interet_ht = ($montantReference * $joursRetard * $taux) / 360.0;
            }

            $interet_ht = round($interet_ht, 2);
            $interet_ttc = round($interet_ht * 1.19, 2);
            // Vérifier s'il existe déjà un intérêt pour cette période
            $existe = \App\Models\Interet::where('releve_id', $this->id)
                ->where('date_debut_periode', $periode['date_debut_periode'])
                ->where('date_fin_periode', $periode['date_fin_periode'])
                ->first();

            if ($existe) {
                $interetsCrees[] = $existe;
                continue;
            }

            $interet = \App\Models\Interet::create([
                'releve_id' => $this->id,
                'facture_id' => null,
                'reference' => 'INT-' . now()->format('Ymd') . '-' . mt_rand(1000, 9999),
                'date_debut_periode' => $periode['date_debut_periode'],
                'date_fin_periode' => $periode['date_fin_periode'],
                'jours_retard' => $joursRetard,
                'interet_ht' => $interet_ht,
                'interet_ttc' => $interet_ttc, // plus utilisé
            ]);

            $interetsCrees[] = $interet;
        }

        return $interetsCrees;
    }
}
