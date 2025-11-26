<?php

namespace App\Services;

use App\Enums\Messages;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CompteService
{

    /**
     * Créer un compte avec token (flux public)
     */
    public function createAccountWithToken(string $token, string $name, string $pin): array
    {
        $accountData = Cache::get('account_creation_' . $token);

        if (!$accountData) {
            return ['success' => false, 'message' => Messages::INVALID_TOKEN->value];
        }

        // Créer l'utilisateur
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $name,
        ]);

        // Créer le client
        $client = Client::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
        ]);

        // Créer le compte
        $compte = Compte::create([
            'id' => Str::uuid(),
            'client_id' => $client->id,
            'telephone' => $accountData['phone'],
            'pin' => $pin,
        ]);

        // Supprimer le token
        Cache::forget('account_creation_' . $token);

        return [
            'success' => true,
            'compte' => $compte,
            'message' => Messages::COMPTE_CREATED->value
        ];
    }

    /**
     * Créer un compte via authentification (nécessite être connecté)
     */
    public function createAccountAuthenticated(User $user, string $telephone, string $pin): array
    {
        $compte = Compte::create([
            'id' => Str::uuid(),
            'client_id' => $user->client->id,
            'telephone' => $telephone,
            'pin' => $pin,
        ]);

        return [
            'success' => true,
            'compte' => $compte,
            'message' => Messages::COMPTE_CREATED->value
        ];
    }

    /**
     * Récupérer le solde d'un compte par ID
     */
    public function getSoldeById(string $compteId, User $user): array
    {
        $compte = Compte::find($compteId);

        if (!$compte) {
            return ['success' => false, 'message' => Messages::COMPTE_NOT_FOUND->value];
        }

        // Vérifier que l'utilisateur a accès à ce compte
        if ($compte->client_id !== $user->client->id) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        return [
            'success' => true,
            'solde' => $compte->solde,
            'compte_id' => $compte->id
        ];
    }

    /**
     * Récupérer le solde d'un compte par numéro de téléphone
     */
    public function getSoldeByTelephone(string $telephone, User $user): array
    {
        $compte = Compte::whereTelephone($telephone)->first();

        if (!$compte) {
            return ['success' => false, 'message' => Messages::COMPTE_NOT_FOUND->value];
        }

        // Vérifier si l'utilisateur a un client associé
        if (!$user->client) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        $isOwner = $compte->client_id === $user->client->id;
        $isAdmin = Admin::where('user_id', $user->id)->exists();

        if (!$isOwner && !$isAdmin) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        return [
            'success' => true,
            'solde' => $compte->solde,
            'compte_id' => $compte->id,
            'telephone' => $compte->telephone
        ];
    }

    /**
     * Générer un QR code pour un compte
     */
    public function generateQrCode(string $compteId, User $user, $qrCodeService): array
    {
        $compte = Compte::find($compteId);

        if (!$compte) {
            return ['success' => false, 'message' => Messages::COMPTE_NOT_FOUND->value];
        }

        if ($compte->client_id !== $user->client->id) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        $qrData = $qrCodeService->generateQrCode($compte);

        return [
            'success' => true,
            'qr_data' => $qrData,
            'message' => 'QR code généré'
        ];
    }

    /**
     * Récupérer les détails complets d'un compte par numéro de téléphone
     */
    public function getCompteByTelephone(string $telephone, User $user): array
    {
        $compte = Compte::whereTelephone($telephone)->first();

        if (!$compte) {
            return ['success' => false, 'message' => Messages::COMPTE_NOT_FOUND->value];
        }

        // Vérifier si l'utilisateur a un client associé
        if (!$user->client) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        $isOwner = $compte->client_id === $user->client->id;
        $isAdmin = Admin::where('user_id', $user->id)->exists();

        if (!$isOwner && !$isAdmin) {
            return ['success' => false, 'message' => Messages::ACCESS_DENIED->value];
        }

        return [
            'success' => true,
            'compte' => $compte
        ];
    }
}