<?php

namespace App\Http\Controllers;

use App\Enums\Messages;
use App\Http\Requests\CreateAccountWithTokenRequest;
use App\Http\Requests\GenerateQrCodeRequest;
use App\Http\Requests\GetCompteByTelephoneRequest;
use App\Http\Requests\SoldeAccountRequest;
use App\Http\Requests\SoldeByTelAccountRequest;
use App\Http\Resources\CompteResource;
use App\Services\CompteService;
use App\Services\QrCodeService;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes utilisateurs"
 * )
 */
class CompteController extends Controller
{
    use \App\ResponseApi;

    protected $qrCodeService;
    protected $compteService;

    public function __construct(QrCodeService $qrCodeService, CompteService $compteService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->compteService = $compteService;
    }

    /**
     * @OA\Post(
     *     path="/auth/create-account",
     *     summary="Créer un nouveau compte avec token ou via authentification",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     required={"token", "name", "pin"},
     *                     @OA\Property(property="token", type="string", example="dy5l0kIMH1XV5SqKA8RLFpBxDMiTFcaimPJxmhYk5UlMEEoQWUsS97Oaiw6S", description="Token de création de compte"),
     *                     @OA\Property(property="name", type="string", example="Test User", description="Nom de l'utilisateur"),
     *                     @OA\Property(property="pin", type="string", example="1234", description="PIN à 4 chiffres")
     *                 ),
     *                 @OA\Schema(
     *                     required={"telephone", "pin"},
     *                     @OA\Property(property="telephone", type="string", example="+221776525959", description="Numéro de téléphone"),
     *                     @OA\Property(property="pin", type="string", example="1234", description="PIN à 4 chiffres")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou token invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide ou expiré")
     *         )
     *     )
     * )
     */
    public function store(CreateAccountWithTokenRequest $request)
    {
        // Vérifier si c'est une création avec token (public) ou via auth (protégé)
        if ($request->has('token')) {
            // Création avec token (flux public)
            return $this->createAccountWithToken($request);
        } else {
            // Création via authentification (nécessite être connecté)
            return $this->createAccountAuthenticated($request);
        }
    }

    /**
     * Créer un compte avec token (flux public)
     */
    private function createAccountWithToken(CreateAccountWithTokenRequest $request)
    {
        $result = $this->compteService->createAccountWithToken(
            $request->token,
            $request->name,
            $request->pin
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse(
            new CompteResource($result['compte']),
            Messages::COMPTE_CREATED->value,
            201
        );
    }

    /**
     * Créer un compte via authentification (nécessite être connecté)
     */
    private function createAccountAuthenticated(CreateAccountWithTokenRequest $request)
    {
        $result = $this->compteService->createAccountAuthenticated(
            $request->user(),
            $request->telephone,
            $request->pin
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse(
            new CompteResource($result['compte']),
            Messages::COMPTE_CREATED->value,
            201
        );
    }

    /**
     * @OA\Post(
     *     path="/generate-qr-code",
     *     summary="Générer un QR code pour les paiements",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="019a6d6f-7770-70a0-a293-cd767103d731", description="ID du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="QR code généré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="QR code généré"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="qr_code", type="string", description="Données du QR code encodées"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", description="Date d'expiration")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé au compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
     *         )
     *     )
     * )
     */
    public function generateQrCode(GenerateQrCodeRequest $request)
    {
        $result = $this->compteService->generateQrCode($request->compte_id, $request->user(), $this->qrCodeService);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], $result['message'] ===  Messages::ACCESS_DENIED->value ? 403 : 404);
        }

        return $this->successResponse($result['qr_data'], $result['message']);
    }

    /**
     * @OA\Post(
     *     path="/get-solde",
     *     summary="Récupérer le solde d'un compte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="019a6d6f-7770-70a0-a293-cd767103d731", description="ID du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solde récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", format="float", example=1500.50, description="Solde du compte"),
     *                 @OA\Property(property="compte_id", type="string", format="uuid", example="019a6d6f-7770-70a0-a293-cd767103d731", description="ID du compte")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé au compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
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
    public function getSolde(SoldeAccountRequest $request)
    {
        $result = $this->compteService->getSoldeById($request->compte_id, $request->user());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], $result['message'] === Messages::ACCESS_DENIED->value ? 403 : 404);
        }

        return $this->successResponse([
            'solde' => $result['solde'],
            'compte_id' => $result['compte_id']
        ], Messages::SOLDE_RECEIVED->value);
    }

    /**
     * @OA\Post(
     *     path="/get-solde-by-telephone",
     *     summary="Récupérer le solde d'un compte par numéro de téléphone",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone"},
     *             @OA\Property(property="telephone", type="string", example="+221776525959", description="Numéro de téléphone du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solde récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", format="float", example=1500.50, description="Solde du compte"),
     *                 @OA\Property(property="compte_id", type="string", format="uuid", example="019a6d6f-7770-70a0-a293-cd767103d731", description="ID du compte"),
     *                 @OA\Property(property="telephone", type="string", example="+221776525959", description="Numéro de téléphone")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé au compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
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
    public function getSoldeByTelephone(SoldeByTelAccountRequest $request)
    {
        $result = $this->compteService->getSoldeByTelephone($request->telephone, $request->user());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], $result['message'] === 'Accès non autorisé' ? 403 : 404);
        }

        return $this->successResponse([
            'solde' => $result['solde'],
            'compte_id' => $result['compte_id'],
            'telephone' => $result['telephone']
        ], Messages::SOLDE_RECEIVED->value);
    }

    /**
     * @OA\Post(
     *     path="/get-compte-by-telephone",
     *     summary="Récupérer les détails complets d'un compte par numéro de téléphone",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone"},
     *             @OA\Property(property="telephone", type="string", example="+221776525959", description="Numéro de téléphone du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails du compte récupérés"),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé au compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès non autorisé")
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
    public function getCompteByTelephone(GetCompteByTelephoneRequest $request)
    {
        $result = $this->compteService->getCompteByTelephone($request->telephone, $request->user());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], $result['message'] === 'Accès non autorisé' ? 403 : 404);
        }

        return $this->successResponse(
            new CompteResource($result['compte']),
            'Détails du compte récupérés'
        );
    }
}
