<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comptes = \App\Models\Compte::all();
        $partenaires = \App\Models\ServicePartenaire::all();

        if ($comptes->count() < 2) {
            return; // Need at least 2 accounts for transactions
        }

        $transactions = [
            [
                'sender_compte_id' => (string) $comptes[0]->id,
                'receiver_client_id' => (string) $comptes[1]->id,
                'montant' => 50000, // 500.00 XOF
                'type' => 'transfert',
                'status' => 'completed',
            ],
            [
                'sender_compte_id' => (string) $comptes[1]->id,
                'receiver_client_id' => (string) $comptes[0]->id,
                'montant' => 25000, // 250.00 XOF
                'type' => 'transfert',
                'status' => 'completed',
            ],
            [
                'sender_compte_id' => (string) $comptes[0]->id,
                'receiver_partenaire_id' => (string) $partenaires[0]->id, // Senelec
                'montant' => 50000, // 500.00 XOF - Electricity bill
                'type' => 'retrait',
                'status' => 'completed',
            ],
            [
                'sender_compte_id' => (string) $comptes[1]->id,
                'receiver_partenaire_id' => (string) $partenaires[1]->id, // Sen'Eau
                'montant' => 25000, // 250.00 XOF - Water bill
                'type' => 'retrait',
                'status' => 'completed',
            ],
            [
                'sender_compte_id' => (string) $comptes[1]->id,
                'montant' => 75000, // 750.00 XOF
                'type' => 'depot',
                'status' => 'completed',
            ],
        ];

        foreach ($transactions as $transactionData) {
            \App\Models\Transaction::create(array_merge($transactionData, ['id' => Str::uuid()]));
        }
    }
}
