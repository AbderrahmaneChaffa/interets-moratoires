<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Client::insert([
            [
                'raison_sociale' => 'Algérie Poste',
                'nif' => '123456789',
                'rc' => 'RC12345',
                'adresse' => '1 Rue de la Poste, Alger',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'raison_sociale' => 'BNA',
                'nif' => '987654321',
                'rc' => 'RC54321',
                'adresse' => '10 Avenue de l’Indépendance, Oran',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'raison_sociale' => 'BEA',
                'nif' => '192837465',
                'rc' => 'RC67890',
                'adresse' => '5 Boulevard des Martyrs, Constantine',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
