<?php

namespace App\Services;

class QrCodeValidationService
{
    /**
     * Validate and decode signed QR code data
     *
     * @param string $qrDataJson The JSON string from QR code
     * @return array|null Returns decoded payload or null if invalid
     */
    public function validateAndDecodeQrData(string $qrDataJson): ?array
    {
        // Decode QR data (format signé)
        $qrData = json_decode($qrDataJson, true);

        if (!$qrData || !isset($qrData['payload']) || !isset($qrData['signature'])) {
            return null; // Format de données QR invalide
        }

        // Vérifier la signature
        $secretKey = env('PAYMENT_QR_SECRET', 'default_qr_secret_key');
        $expectedSignature = hash_hmac('sha256', $qrData['payload'], $secretKey);

        if (!hash_equals($expectedSignature, $qrData['signature'])) {
            return null; // Signature QR invalide - données compromises
        }

        // Décoder les données payload
        $payload = json_decode($qrData['payload'], true);

        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'account') {
            return null; // Données QR invalides
        }

        // Vérifier que le QR n'est pas expiré (optionnel, 5 minutes)
        if (isset($payload['timestamp']) && (now()->timestamp - $payload['timestamp']) > 300) {
            return null; // QR code expiré
        }

        return $payload;
    }

    /**
     * Check if QR code data is valid for transactions
     *
     * @param string $qrDataJson The JSON string from QR code
     * @return bool
     */
    public function isValidForTransaction(string $qrDataJson): bool
    {
        $payload = $this->validateAndDecodeQrData($qrDataJson);
        return $payload !== null;
    }

    /**
     * Extract account ID from valid QR code data
     *
     * @param string $qrDataJson The JSON string from QR code
     * @return string|null
     */
    public function extractAccountId(string $qrDataJson): ?string
    {
        $payload = $this->validateAndDecodeQrData($qrDataJson);
        return $payload ? $payload['compte_id'] : null;
    }
}