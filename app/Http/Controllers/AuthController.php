<?php

namespace App\Http\Controllers;

use App\Enums\Messages;
use App\Http\Requests\MagicLink;
use App\Http\Requests\MagicLinkMarchant;
use App\Http\Requests\VerifyAccount;
use App\Http\Requests\VerifyMarchantAccount;
use App\Http\Resources\AuthResource;
use App\Jobs\SendSmsJob;
use App\Models\Marchant;
use App\ResponseApi;
use App\Services\AuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Gestion des authentifications"
 * )
 * 
 * @OA\Info(
 *     title="OM Pay API",
 *     version="1.0.0",
 *     description="API de paiement mobile OM Pay"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:9000/api",
 *     description="Serveur de développement"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    use ResponseApi;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/send-magic-link",
     *     summary="Envoyer un lien magique par SMS",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+221777065468", description="Numéro de téléphone au format international")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lien magique généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lien magique généré"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="link", type="string", example="myapp://auth/enter-pin?token=abc123"),
     *                 @OA\Property(property="token", type="string", example="abc123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Numéro de téléphone non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Téléphone non trouvé")
     *         )
     *     )
     * )
     */
    public function sendMagicLink(MagicLink $request)
    {
        /** @var MagicLink $request */
        $validated = $request->validated();
        $result = $this->authService->sendMagicLink($validated['phone']);

        if ($result['success']) {
            return $this->successResponse(
                [
                    'link' => $result['link'],
                    'token' => $result['token'],
                    'requires_account_creation' => $result['requires_account_creation']
                ],
                $result['message']
            );
        }

        return $this->errorResponse(
            $result['message'],
            404
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-pin",
     *     summary="Vérifier le PIN et obtenir un token d'accès",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","pin"},
     *             @OA\Property(property="token", type="string", example="abc123", description="Token reçu par SMS"),
     *             @OA\Property(property="pin", type="string", example="1234", description="PIN à 4 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentification réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1Qi...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="PIN invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="PIN invalide")
     *         )
     *     )
     * )
     */
    public function verifyPin(VerifyAccount $request)
    {
        /** @var VerifyAccount $request */
        $validated = $request->validated();
        $result = $this->authService->verifyPin($validated['token'], $validated['pin']);

        if ($result) {
            return $this->successResponse(
                new AuthResource($result),
                Messages::LOGIN_SUCCESS->value
            );
        }

        return $this->errorResponse(
            Messages::INVALID_PIN->value,
            401
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/send-magic-link-merchant",
     *     summary="Envoyer un lien magique pour la connexion marchant",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code_marchant"},
     *             @OA\Property(property="code_marchant", type="string", example="MCH-000012", description="Code marchand")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lien magique envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lien magique envoyé"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="link", type="string", description="Lien de connexion"),
     *                 @OA\Property(property="token", type="string", description="Token de session")
     *             )
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
    public function sendMagicLinkMerchant(MagicLinkMarchant $request)
    {
        /** @var MagicLinkMarchant $request */
        $validated = $request->validated();
        $marchant = Marchant::whereCode($validated['code_marchant'])->active()->first();

        if (!$marchant) {
            return $this->errorResponse(Messages::MARCHANT_NOT_FOUND->value, 404);
        }

        $token = Str::random(60);

        Cache::put('magic_link_merchant_' . $token, $marchant->id, now()->addMinutes(5));

        $link = "myapp://auth/merchant/enter-pin?token={$token}";

        SendSmsJob::dispatch($marchant->telephone_service, Messages::OPEN_LINK->value.$link);

        return $this->successResponse(
            [
                'link' => $link,
                'token' => $token
            ],
            Messages::MAGIC_LINK_SENT->value
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-pin-merchant",
     *     summary="Vérifier le PIN pour la connexion marchant",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","pin"},
     *             @OA\Property(property="token", type="string", description="Token de session"),
     *             @OA\Property(property="pin", type="string", example="1234", description="PIN à 4 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", description="Token d'accès JWT"),
     *                 @OA\Property(property="marchant", ref="#/components/schemas/Marchant")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="PIN invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="PIN invalide")
     *         )
     *     )
     * )
     */
    public function verifyPinMerchant(VerifyMarchantAccount $request)
    {
        /** @var VerifyMarchantAccount $request */
        $validated = $request->validated();
        $marchantId = Cache::get('magic_link_merchant_' . $validated['token']);

        if (!$marchantId) {
            return $this->errorResponse(Messages::TOKEN_EXPIRED_INVALID->value, 401);
        }

        $marchant = Marchant::find($marchantId);

        if (!$marchant) {
            return $this->errorResponse(Messages::MARCHANT_NOT_FOUND->value, 404);
        }

        if ($validated['pin'] !== '0000') {
            return $this->errorResponse(Messages::INVALID_PIN->value, 401);
        }

        Cache::forget('magic_link_merchant_' . $validated['token']);

        $tokenResult = $marchant->user->createToken('Merchant API Token');
        $accessToken = $tokenResult->accessToken;

        return $this->successResponse(
            [
                'access_token' => $accessToken,
                'marchant' => $marchant
            ],
            Messages::LOGIN_SUCCESS->value
        );
    }
}