<?php

namespace App\Observers;

use App\Models\Marchant;
use App\Services\QrCodeService;

class MarchantObserver
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Handle the Marchant "created" event.
     */
    public function created(Marchant $marchant): void
    {
        if (empty($marchant->code_merchant)) {
            $marchant->update([
                'code_merchant' => 'MCH-' . str_pad($marchant->id, 6, '0', STR_PAD_LEFT)
            ]);
        }

        if (empty($marchant->url_qr)) {
            $qrCodeSvg = $this->qrCodeService->generateMerchantQrCode(
                $marchant->code_merchant,
                $marchant->nom_boutique
            );

            $filename = 'merchant_' . $marchant->code_merchant;
            $qrUrl = $this->qrCodeService->saveQrCodeToFile($qrCodeSvg, $filename);

            $marchant->update([
                'url_qr' => $qrUrl
            ]);
        }
    }

    /**
     * Handle the Marchant "updated" event.
     */
    public function updated(Marchant $marchant): void
    {
        //
    }

    /**
     * Handle the Marchant "deleted" event.
     */
    public function deleted(Marchant $marchant): void
    {
        //
    }

    /**
     * Handle the Marchant "restored" event.
     */
    public function restored(Marchant $marchant): void
    {
        //
    }

    /**
     * Handle the Marchant "force deleted" event.
     */
    public function forceDeleted(Marchant $marchant): void
    {
        //
    }
}
