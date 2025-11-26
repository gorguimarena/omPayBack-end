<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\MarchantController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/send-magic-link', [AuthController::class, 'sendMagicLink']);
    Route::post('/verify-pin', [AuthController::class, 'verifyPin']);
    Route::post('/create-account', [CompteController::class, 'store']);


    Route::post('/send-magic-link-merchant', [AuthController::class, 'sendMagicLinkMerchant']);
    Route::post('/verify-pin-merchant', [AuthController::class, 'verifyPinMerchant']);
});

// Route::prefix('marchants')->group(function () {
//     Route::get('/', [MarchantController::class, 'index']);
//     Route::get('/search/{code}', [MarchantController::class, 'searchByCode']);
//     Route::get('/{marchant}', [MarchantController::class, 'show']);
// });

Route::middleware('auth:api')->group(function () {
    // Compte 
    Route::post('/generate-qr-code', [CompteController::class, 'generateQrCode']);
    Route::post('/get-solde', [CompteController::class, 'getSolde']);
    Route::post('/get-solde-by-telephone', [CompteController::class, 'getSoldeByTelephone']);
    Route::post('/get-compte-by-telephone', [CompteController::class, 'getCompteByTelephone']);

    // Marchant
    Route::post('/marchants', [MarchantController::class, 'store']);
    Route::get('/marchants/dashboard', [MarchantController::class, 'dashboard']);

    // Transaction
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{transaction}', [TransactionController::class, 'show']);
        Route::post('/get-by-compte', [TransactionController::class, 'getTransactionsByCompte']);
        Route::post('/depot', [TransactionController::class, 'depot']);
        Route::post('/retrait', [TransactionController::class, 'retrait']);
        Route::post('/transfert', [TransactionController::class, 'transfert']);
        Route::post('/transfert-telephone', [TransactionController::class, 'transfertByTelephone']);
        Route::post('/achat-marchant', [TransactionController::class, 'achatMarchant']);
        Route::post('/transfert-qr', [TransactionController::class, 'transfertViaQr']);
    });
});

