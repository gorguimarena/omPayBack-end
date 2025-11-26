<?php

namespace App\Http\Controllers;

use App\Http\Requests\AchatRequest;
use App\Http\Requests\DepotRequest;
use App\Http\Requests\DepotTelephoneRequest;
use App\Http\Requests\GetTransactionsByCompteRequest;
use App\Http\Requests\RetraitRequest;
use App\Http\Requests\TransfertByTelephone;
use App\Http\Requests\TransfertQrRequest;
use App\Http\Requests\TransfertRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Compte;
use App\Models\Marchant;
use App\Models\Transaction;
use App\ResponseApi;
use App\Services\QrCodeValidationService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Opérations de paiement et transferts"
 * )
 */
class TransactionController extends Controller
{
    use ResponseApi;

    protected $transactionService;
    protected $qrCodeValidationService;

    public function __construct(
        TransactionService $transactionService,
        QrCodeValidationService $qrCodeValidationService
    ) {
        $this->transactionService = $transactionService;
        $this->qrCodeValidationService = $qrCodeValidationService;
    }

    /**
     * Détermine si l'identifiant est un code marchand ou un numéro de téléphone
     *
     * @param string $identifier
     * @return array ['type' => 'code|phone', 'is_code' => bool, 'is_phone' => bool]
     */
    private function identifyMerchantIdentifier(string $identifier): array
    {
        $isCode = preg_match('/^MCH-\d{6}$/', $identifier);
        $isPhone = preg_match('/^\+221[0-9]{9}$/', $identifier);

        return [
            'type' => $isCode ? 'code' : ($isPhone ? 'phone' : 'invalid'),
            'is_code' => $isCode,
            'is_phone' => $isPhone,
            'is_valid' => $isCode || $isPhone
        ];
    }

    /**
     * @OA\Get(
     *     path="/transactions",
     *     summary="Récupérer l'historique des transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique des transactions récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Historique des transactions récupéré"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Transaction")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::forUser($user)
            ->with(['senderCompte', 'destinataireCompte', 'agent'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse(
            TransactionResource::collection($transactions),
            'Historique des transactions récupéré'
        );
    }

    /**
     * @OA\Get(
     *     path="/transactions/{transaction}",
     *     summary="Récupérer les détails d'une transaction",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         description="ID de la transaction",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la transaction",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails de la transaction"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Transaction not found")
     *         )
     *     )
     * )
     */
    public function show(Transaction $transaction)
    {
        return $this->successResponse(
            new TransactionResource($transaction->load(['senderCompte', 'destinataireCompte'])),
            'Détails de la transaction'
        );
    }

    /**
     * @OA\Post(
     *     path="/transactions/get-by-compte",
     *     summary="Récupérer les transactions d'un compte par son ID",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9", description="ID du compte"),
     *             @OA\Property(property="limit", type="integer", example=20, description="Nombre maximum de transactions à récupérer (1-100)"),
     *             @OA\Property(property="offset", type="integer", example=0, description="Décalage pour la pagination"),
     *             @OA\Property(property="type", type="string", enum={"depot", "retrait", "transfert", "achat"}, example="transfert", description="Filtrer par type de transaction"),
     *             @OA\Property(property="date_from", type="string", format="date", example="2025-01-01", description="Date de début (YYYY-MM-DD)"),
     *             @OA\Property(property="date_to", type="string", format="date", example="2025-12-31", description="Date de fin (YYYY-MM-DD)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transactions récupérées avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(ref="#/components/schemas/Transaction")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="limit", type="integer", example=20),
     *                 @OA\Property(property="offset", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé au compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function getTransactionsByCompte(GetTransactionsByCompteRequest $request)
    {
        $result = $this->transactionService->getTransactionsByCompte(
            $request->user(),
            $request->compte_id,
            $request->only(['limit', 'offset', 'type', 'date_from', 'date_to'])
        );

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Accès non autorisé à ce compte' ? 403 : 404;
            return $this->errorResponse($result['message'], $statusCode);
        }

        return $this->successResponse(
            [
                'transactions' => TransactionResource::collection($result['transactions']),
                'total' => $result['total'],
                'limit' => $result['limit'],
                'offset' => $result['offset']
            ],
            'Transactions récupérées avec succès'
        );
    }

    /**
     * Make a deposit (dépôt)
     */
    public function depot(DepotRequest $request)
    {
        $result = $this->transactionService->depot(
            $request->user(),
            $request->compte_id,
            $request->montant
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                $result['message']
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * Make a withdrawal (retrait)
     */
    public function retrait(RetraitRequest $request)
    {
        $result = $this->transactionService->retrait(
            $request->user(),
            $request->compte_id,
            $request->partenaire_id,
            $request->montant
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                $result['message']
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * @OA\Post(
     *     path="/transactions/transfert",
     *     summary="Effectuer un transfert entre comptes",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sender_compte_id","receiver_compte_id","montant"},
     *             @OA\Property(property="sender_compte_id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9", description="ID du compte expéditeur"),
     *             @OA\Property(property="receiver_compte_id", type="string", format="uuid", example="8cd9334d-4fac-49b3-b1b5-9e91656b9350", description="ID du compte destinataire"),
     *             @OA\Property(property="montant", type="number", format="float", example=500.00, description="Montant à transférer (frais de 2 FCFA inclus)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant (montant + frais de 2 FCFA)")
     *         )
     *     )
     * )
     */
    public function transfert(TransfertRequest $request)
    {
        $result = $this->transactionService->transfert(
            $request->user(),
            $request->sender_compte_id,
            $request->receiver_compte_id,
            $request->montant
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                $result['message']
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * @OA\Post(
     *     path="/transactions/transfert-telephone",
     *     summary="Effectuer un transfert par numéro de téléphone",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sender_compte_id","telephone","montant"},
     *             @OA\Property(property="sender_compte_id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9", description="ID du compte expéditeur"),
     *             @OA\Property(property="telephone", type="string", example="+221777065435", description="Numéro de téléphone du destinataire"),
     *             @OA\Property(property="montant", type="number", format="float", example=100.00, description="Montant à transférer (frais de 2 FCFA inclus)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction"),
     *             @OA\Property(property="frais", type="number", format="float", example=2.00),
     *             @OA\Property(property="montant_total", type="number", format="float", example=502.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant (montant + frais de 2 FCFA)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Numéro de téléphone non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun compte trouvé avec ce numéro de téléphone")
     *         )
     *     )
     * )
     */
    public function transfertByTelephone(TransfertByTelephone $request)
    {
        $result = $this->transactionService->transfertByTelephone(
            $request->user(),
            $request->sender_compte_id,
            $request->telephone,
            $request->montant
        );

        if ($result['success']) {
            $responseData = new TransactionResource($result['transaction']);
            $responseData->additional([
                'frais' => $result['frais'] ?? 2.00,
                'montant_total' => $result['montant_total'] ?? ($request->montant + 2.00)
            ]);

            return $this->successResponse(
                $responseData,
                $result['message']
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * @OA\Post(
     *     path="/transactions/achat-marchant",
     *     summary="Effectuer un achat chez un marchant",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sender_compte_id","code_marchant","montant"},
     *             @OA\Property(property="sender_compte_id", type="string", format="uuid", example="019a6d6f-7770-70a0-a293-cd767103d731", description="ID du compte client"),
     *             @OA\Property(property="code_marchant", type="string", example="MCH-123456", description="Code marchand (format MCH-XXXXXX) ou numéro de téléphone du service partenaire (format +221XXXXXXXXX)"),
     *             @OA\Property(property="montant", type="number", format="float", example=1500.00, description="Montant de l'achat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Achat effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Achat effectué avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marchant non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Marchant non trouvé")
     *         )
     *     )
     * )
     */
    public function achatMarchant(AchatRequest $request)
    {
        $codeMarchant = $request->code_marchant;
        $identifier = $this->identifyMerchantIdentifier($codeMarchant);

        if (!$identifier['is_valid']) {
            return $this->errorResponse('Format invalide. Utilisez un code marchand (MCH-XXXXXX) ou un numéro de téléphone (+221XXXXXXXXX)', 400);
        }

        if ($identifier['is_code']) {
            $marchant = Marchant::whereCode($codeMarchant)->active()->first();
            $errorMessage = 'Marchant non trouvé avec ce code';
        } elseif ($identifier['is_phone']) {
            $marchant = Marchant::where('telephone_service', $codeMarchant)->active()->first();
            $errorMessage = 'Marchant non trouvé avec ce numéro de téléphone';
        }

        if (!$marchant) {
            return $this->errorResponse($errorMessage, 404);
        }

        $result = $this->transactionService->transfert(
            $request->user(),
            $request->sender_compte_id,
            null,
            $request->montant,
            $marchant->id
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                'Achat effectué avec succès'
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * Make a transfer using QR code data
     */
    public function transfertViaQr(TransfertQrRequest $request)
    {
        $payload = $this->qrCodeValidationService->validateAndDecodeQrData($request->qr_data);

        if (!$payload) {
            return $this->errorResponse('Données QR invalides ou expirées', 400);
        }

        $receiverCompteId = $payload['compte_id'];

        $result = $this->transactionService->transfert(
            $request->user(),
            $request->sender_compte_id,
            $receiverCompteId,
            $request->montant
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                'Transfert via QR code effectué avec succès'
            );
        }

        return $this->errorResponse($result['message'], 400);
    }

    /**
     * Make a deposit using phone number
     */
    public function depotViaTelephone(DepotTelephoneRequest $request)
    {
        $receiverCompte = Compte::whereTelephone($request->telephone)->first();

        if (!$receiverCompte) {
            return $this->errorResponse('Numéro de téléphone non trouvé', 404);
        }

        $result = $this->transactionService->depot(
            $request->user(),
            $receiverCompte->id,
            $request->montant
        );

        if ($result['success']) {
            return $this->successResponse(
                new TransactionResource($result['transaction']),
                'Dépôt via numéro de téléphone effectué avec succès'
            );
        }

        return $this->errorResponse($result['message'], 400);
    }
}
