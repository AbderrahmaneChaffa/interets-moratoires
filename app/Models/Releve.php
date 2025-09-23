<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Releve extends Model
{
    use HasFactory;
    use SoftDeletes;

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
            ->filter(function ($f) {
                return ($f->statut !== 'Payée');
            })
            ->sum('montant_ht');

        // Date de dépôt du relevé = date de la dernière facture incluse
        $dateDepot = $this->factures->max('date_depot') ?: $this->factures->max('date_facture');
        if (!$dateDepot) {
            return [];
        }

        $dateDepot = $dateDepot instanceof \Carbon\Carbon ? $dateDepot : \Carbon\Carbon::parse($dateDepot);

        // On génère une seule séquence de périodes bornées par [date_depot + delai, date_fin]
        // en réutilisant la logique de génération sur une facture "virtuelle": on prend la première facture pour le délai légal
        $factureRef = $this->factures->first();
        if (!$factureRef) {
            return [];
        }

        $delai = $factureRef->delai_legal_jours ?? 30;
        $dateDebutGrace = $dateDepot->copy()->addDays($delai);
        $dateFin = $this->date_fin instanceof \Carbon\Carbon ? $this->date_fin : \Carbon\Carbon::parse($this->date_fin);

        if ($dateFin <= $dateDebutGrace) {
            return [];
        }

        // Construction des périodes mensuelles entre dateDebutGrace et dateFin
        $periodes = [];
        $mois = 1;
        $curseur = $dateDebutGrace->copy();
        while ($curseur < $dateFin) {
            $debut = $curseur->copy();
            $fin = $debut->copy()->addMonth();
            if ($fin > $dateFin) {
                $fin = $dateFin->copy();
            }
            $periodes[] = [
                'mois' => $mois,
                'date_debut_periode' => $debut,
                'date_fin_periode' => $fin,
            ];
            $mois++;
            $curseur = $fin->copy();
        }

        // Application des règles métier avec le montant agrégé et les jours de retard de chaque période
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
                    $interet_ht = $montantReference * 0.05 * 1; // par mois
                    break;
                case str_contains($clientName, 'BNA'):
                case str_contains($clientName, 'BDL'):
                case str_contains($clientName, 'CNEP'):
                    $interet_ht = $montantReference * 0.10 * 1; // par mois
                    break;
                default:
                    $taux = (float) ($this->client->taux ?? 0.1);
                    $interet_ht = ($montantReference * $joursRetard * $taux) / 360.0;
            }

            $interet_ht = round($interet_ht, 2);
            $interet_ttc = round($interet_ht * 1.19, 2);

            // Eviter les doublons par période
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
                'date_debut_periode' => $periode['date_debut_periode'],
                'date_fin_periode' => $periode['date_fin_periode'],
                'jours_retard' => $joursRetard,
                'interet_ht' => $interet_ht,
                'interet_ttc' => $interet_ttc,
            ]);

            $interetsCrees[] = $interet;
        }

        return $interetsCrees;
    }
}