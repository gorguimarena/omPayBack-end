<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    private const TRANSFERT_FEES = 2.00; 

    /**
     * Validate account ownership
     */
    private function validateAccountOwnership(User $user, string $compteId): ?Compte
    {
        $compte = Compte::find($compteId);

        if (!$compte || $compte->client_id !== $user->client->id) {
            return null;
        }

        return $compte;
    }

    /**
     * Make a deposit (dépôt) - Add money to account
     */
    public function depot(User $user, string $compteId, float $montant): array
    {
        $compte = $this->validateAccountOwnership($user, $compteId);

        if (!$compte) {
            return [
                'success' => false,
                'message' => 'Compte non trouvé ou accès non autorisé'
            ];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'sender_compte_id' => $compte->id,
                'montant' => $montant,
                'type' => 'depot',
                'status' => 'completed',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dépôt effectué avec succès',
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du dépôt'
            ];
        }
    }

    /**
     * Make a withdrawal (retrait) - Pay bills to service partners
     */
    public function retrait(User $user, string $compteId, string $partenaireId, float $montant): array
    {
        $compte = $this->validateAccountOwnership($user, $compteId);

        if (!$compte) {
            return [
                'success' => false,
                'message' => 'Compte non trouvé ou accès non autorisé'
            ];
        }

        $currentBalance = $compte->solde;
        if ($currentBalance < $montant) {
            return [
                'success' => false,
                'message' => 'Solde insuffisant'
            ];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'sender_compte_id' => $compte->id,
                'receiver_partenaire_id' => $partenaireId,
                'montant' => $montant,
                'type' => 'retrait',
                'status' => 'completed',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du paiement'
            ];
        }
    }

    /**
     * Make a transfer (transfert) - Transfer money between accounts or to merchants
     */
    public function transfert(User $user, string $senderCompteId, ?string $receiverCompteId, float $montant, ?int $marchantId = null): array
    {
        $senderCompte = $this->validateAccountOwnership($user, $senderCompteId);

        if (!$senderCompte) {
            return [
                'success' => false,
                'message' => 'Compte expéditeur non trouvé ou accès non autorisé'
            ];
        }

        if ($marchantId) {
            $totalAmount = $montant; 
            $transactionData = [
                'sender_compte_id' => $senderCompte->id,
                'receiver_marchant_id' => $marchantId,
                'montant' => $montant,
                'type' => 'achat', 
                'status' => 'completed',
            ];
        } else {
            $receiverCompte = Compte::find($receiverCompteId);

            if (!$receiverCompte) {
                return [
                    'success' => false,
                    'message' => 'Compte destinataire non trouvé'
                ];
            }

            $totalAmount = $montant + self::TRANSFERT_FEES;
            $transactionData = [
                'sender_compte_id' => $senderCompte->id,
                'receiver_client_id' => $receiverCompte->id,
                'montant' => $montant,
                'type' => 'transfert',
                'status' => 'completed',
            ];
        }

        $currentBalance = $senderCompte->solde;
        if ($currentBalance < $totalAmount) {
            return [
                'success' => false,
                'message' => $marchantId ? 'Solde insuffisant' : 'Solde insuffisant (montant + frais de 2 FCFA)'
            ];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create($transactionData);

            DB::commit();

            $message = $marchantId ? 'Achat effectué avec succès' : 'Transfert effectué avec succès';
            return [
                'success' => true,
                'message' => $message,
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'opération'
            ];
        }
    }

    /**
     * Make a transfer by phone number - Transfer money using recipient's phone number
     */
    public function transfertByTelephone(User $user, string $senderCompteId, string $recipientPhone, float $montant): array
    {
        $senderCompte = $this->validateAccountOwnership($user, $senderCompteId);

        if (!$senderCompte) {
            return [
                'success' => false,
                'message' => 'Compte expéditeur non trouvé ou accès non autorisé'
            ];
        }

        // Find recipient account by phone number
        $recipientCompte = Compte::whereTelephone($recipientPhone)->first();

        if (!$recipientCompte) {
            return [
                'success' => false,
                'message' => 'Aucun compte trouvé avec ce numéro de téléphone'
            ];
        }

        // Check if sender and recipient are the same account
        if ($senderCompte->id === $recipientCompte->id) {
            return [
                'success' => false,
                'message' => 'Impossible de transférer vers le même compte'
            ];
        }

        $totalAmount = $montant + self::TRANSFERT_FEES;

        $currentBalance = $senderCompte->solde;
        if ($currentBalance < $totalAmount) {
            return [
                'success' => false,
                'message' => 'Solde insuffisant (montant + frais de 2 FCFA)'
            ];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'sender_compte_id' => $senderCompte->id,
                'receiver_client_id' => $recipientCompte->id,
                'montant' => $montant,
                'type' => 'transfert',
                'status' => 'completed',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transfert effectué avec succès',
                'transaction' => $transaction,
                'frais' => self::TRANSFERT_FEES,
                'montant_total' => $totalAmount
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du transfert'
            ];
        }
    }

    /**
     * Get transactions for a specific account with filtering and pagination
     */
    public function getTransactionsByCompte(User $user, string $compteId, array $filters = []): array
    {
        // Validate account ownership
        $compte = $this->validateAccountOwnership($user, $compteId);

        if (!$compte) {
            return [
                'success' => false,
                'message' => 'Accès non autorisé à ce compte'
            ];
        }

        // Set default values
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        $type = $filters['type'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        // Build query for transactions where the account is either sender or receiver
        $query = Transaction::where(function ($q) use ($compteId) {
            $q->where('sender_compte_id', $compteId)
              ->orWhere('receiver_client_id', $compteId);
        });

        // Apply filters
        if ($type) {
            $query->where('type', $type);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination and ordering
        $transactions = $query->with(['senderCompte', 'destinataireCompte', 'agent'])
                              ->orderBy('created_at', 'desc')
                              ->limit($limit)
                              ->offset($offset)
                              ->get();

        return [
            'success' => true,
            'transactions' => $transactions,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}