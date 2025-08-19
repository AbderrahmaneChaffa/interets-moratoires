<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'raison_sociale' => 'ALGERIE POSTE',
                'contrat_maintenance' => 'AP 612 GAB',
                'formule' => '(MONTANT DE LA FACTURE x NOMBRE DE JOURS DE RETARD x TAUX)/ 360',
                'taux' => 0.09, // 9%
                'nif' => '00021600210442',
                'rc' => '02 B 00210044',
                'ai' => '16013948101',
                'adresse' => 'ILOT 01 PASSERELLE N.04 ZONE D\'AFFAIRES BAB EZZOUAR ALGER',
            ],
            [
                'raison_sociale' => 'CPA DIRECTION MONETIQUE',
                'contrat_maintenance' => 'CPA 48 GAB',
                'formule' => '(MONTANT DE LA FACTURE x 05% x MOIS DE RETARD)',
                'taux' => 0.05, // 5%
                'nif' => '99916000929234',
                'rc' => '99 B 0009292',
                'ai' => null,
                'adresse' => '50 RUE DES TROIS FRERES BOUADDOU, BIR MOURAD RAIS',
            ],
            [
                'raison_sociale' => 'BNA',
                'contrat_maintenance' => 'BNA NCR P86,SS34,SS22,SS26',
                'formule' => '(MONTANT DE LA FACTURE x 10 % x MOIS DE RETARD)',
                'taux' => 0.10, // 10%
                'nif' => '000016001290414',
                'rc' => '00 B 12904',
                'ai' => '16070801035',
                'adresse' => '27, RUE HOCINE BOUCHACHI  BOUZAREAH ALGER',
            ],
            [
                'raison_sociale' => 'BDL',
                'contrat_maintenance' => 'NCR 67 DAB',
                'formule' => '(MONTANT DE LA FACTURE x 10 % x MOIS DE RETARD)',
                'taux' => 0.10, // 10%
                'nif' => '00001600145493',
                'rc' => '00 B 14054',
                'ai' => '4229003891',
                'adresse' => '5, RUE GACI AMAR STAOULI ALGER ALGERRIE',
            ],
            [
                'raison_sociale' => 'CNEP',
                'contrat_maintenance' => 'CNEP N NCR 30 DAB',
                'formule' => '(MONTANT DE LA FACTURE x 10 % x MOIS DE RETARD)',
                'taux' => 0.10, // 10%
                'nif' => '000016001382940',
                'rc' => '16 00-0013829 B 00',
                'ai' => '16025322002',
                'adresse' => '12, RUE KACI MOHAMED BABA HASSEN ALGER, ALGERIE',
            ],
        ];

        foreach ($clients as $data) {
            Client::updateOrCreate(
                [
                    'raison_sociale' => $data['raison_sociale'],
                    'rc' => $data['rc'],
                ],
                $data + ['updated_at' => now()]
            );
        }
    }
}
