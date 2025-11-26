<?php

namespace App\Services;

use App\Enums\Messages;
use App\Jobs\SendSmsJob;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    use \App\ResponseApi;

    public function sendMagicLink(string $phone): array
    {
        $compte = Compte::whereTelephone($phone)->first();

        if (!$compte) {
            $token = Str::random(60);
            $magicLinkToken = Str::random(32);

            Cache::put('account_creation_' . $token, [
                'phone' => $phone,
                'magic_link_token' => $magicLinkToken
            ], now()->addMinutes(30));

            $link = "myapp://auth/create-account?token={$token}&magic_link={$magicLinkToken}";

            SendSmsJob::dispatch($phone, "Créez votre compte en ouvrant ce lien : $link");

            return [
                'success' => true,
                'link' => $link,
                'token' => $token,
                'message' => 'Lien de création de compte envoyé',
                'requires_account_creation' => true
            ];
        }

        $token = Str::random(60);

        Cache::put('magic_link_' . $token, $compte->id, now()->addMinutes(5));

        $link = "myapp://auth/enter-pin?token={$token}";

        SendSmsJob::dispatch($phone, "Ouvre ce lien pour te connecter : $link");

        return [
            'success' => true,
            'link' => $link,
            'token' => $token,
            'message' => 'Lien magique généré',
            'requires_account_creation' => false
        ];
    }

    public function verifyPin(string $token, string $pin): ?array
    {
        $compteId = Cache::get('magic_link_' . $token);

        if (!$compteId) {
            return null;
        }

        $compte = Compte::find($compteId);

        if (!$compte || !Hash::check($pin, $compte->pin)) {
            return null;
        }

        Cache::forget('magic_link_' . $token);

        $tokenResult = $compte->client->user->createToken('API Token');
        $accessToken = $tokenResult->accessToken;

        return [
            'access_token' => $accessToken,
            'refresh_token' => null,
            'token_type' => 'personal_access',
        ];
    }

    public function createAccountFromToken(string $token, array $accountData): array
    {
        $cachedData = Cache::get('account_creation_' . $token);

        if (!$cachedData) {
            return ['success' => false, 'message' => 'Token invalide ou expiré'];
        }

        // Create user first
        $user = User::create([
            'name' => $accountData['name'],
            'password' => bcrypt('default_password'),
        ]);

        // Create client
        $client = Client::create([
            'user_id' => $user->id,
        ]);

        // Create compte
        $compte = Compte::create([
            'client_id' => $client->id,
            'telephone' => $cachedData['phone'],
            'pin' => $accountData['pin'],
        ]);

        // Clear cache
        Cache::forget('account_creation_' . $token);

        // Generate QR code for the new account
        $qrCodeService = new QrCodeService();
        $qrCode = $qrCodeService->generateAccountQrCode($compte->id, $cachedData['phone']);

        // Send QR code via SMS
        SendSmsJob::dispatch(
            $cachedData['phone'],
            "Votre compte a été créé avec succès! Voici votre QR code d'accès : " . $qrCode
        );

        return [
            'success' => true,
            'message' => 'Compte créé avec succès',
            'qr_code' => $qrCode,
            'compte' => $compte
        ];
    }
}
