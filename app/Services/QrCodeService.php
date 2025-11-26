<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function generateQrCode(string $data, int $size = 300, int $margin = 10): string
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    public function generateAccountQrCode(string $compteId, string $telephone, int $size = 200): string
    {
        $data = json_encode([
            'type' => 'account',
            'compte_id' => $compteId,
            'telephone' => $telephone,
            'action' => 'transaction',
            'timestamp' => now()->timestamp
        ]);

        // Ajouter une signature HMAC pour sécuriser les données
        $secretKey = env('PAYMENT_QR_SECRET', 'default_qr_secret_key');
        $signature = hash_hmac('sha256', $data, $secretKey);

        // Créer les données signées
        $dataSigned = json_encode([
            'payload' => $data,
            'signature' => $signature
        ]);

        return $this->generateQrCode($dataSigned, $size);
    }

    public function generateMerchantQrCode(string $merchantCode, string $merchantName, int $size = 250): string
    {
        $data = json_encode([
            'type' => 'merchant',
            'code' => $merchantCode,
            'name' => $merchantName,
            'action' => 'payment',
            'timestamp' => now()->timestamp
        ]);

        // Ajouter une signature HMAC pour sécuriser les données
        $secretKey = env('PAYMENT_QR_SECRET', 'default_qr_secret_key');
        $signature = hash_hmac('sha256', $data, $secretKey);

        // Créer les données signées
        $dataSigned = json_encode([
            'payload' => $data,
            'signature' => $signature
        ]);

        return $this->generateQrCode($dataSigned, $size);
    }

    public function saveQrCodeToFile(string $qrCodeSvg, string $filename): string
    {
        $path = storage_path('app/public/qr-codes/' . $filename . '.svg');

        // Créer le répertoire s'il n'existe pas
        $directory = dirname($path);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Sauvegarder le fichier SVG
        file_put_contents($path, $qrCodeSvg);

        // Retourner l'URL publique
        return asset('storage/qr-codes/' . $filename . '.svg');
    }

}
