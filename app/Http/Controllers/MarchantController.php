<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMarchantRequest;
use App\Http\Resources\MarchantResource;
use App\Models\Marchant;
use App\ResponseApi;
use Illuminate\Http\Request;

class MarchantController extends Controller
{

    use ResponseApi;

    protected $qrCodeService;

    public function __construct(\App\Services\QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/marchants/search/{code}",
     *     summary="Rechercher un marchant par code",
     *     tags={"Marchants"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Code marchand (ex: MCH-000012)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Marchant trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Marchant trouvé"),
     *             @OA\Property(property="data", ref="#/components/schemas/Marchant")
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
    public function searchByCode($code)
    {
        $marchant = Marchant::whereCode($code)->active()->first();

        if (!$marchant) {
            return $this->errorResponse('Marchant non trouvé', 404);
        }

        return $this->successResponse(
            new MarchantResource($marchant),
            'Marchant trouvé'
        );
    }

    /**
     * @OA\Get(
     *     path="/marchants/dashboard",
     *     summary="Tableau de bord du marchant connecté",
     *     tags={"Marchants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Données du tableau de bord",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tableau de bord"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="marchant", ref="#/components/schemas/Marchant"),
     *                 @OA\Property(property="transactions_today", type="integer", example=15),
     *                 @OA\Property(property="total_amount_today", type="number", format="float", example=150000.00),
     *                 @OA\Property(property="transactions_this_month", type="integer", example=450),
     *                 @OA\Property(property="total_amount_this_month", type="number", format="float", example=4500000.00)
     *             )
     *         )
     *     )
     * )
     */
    public function dashboard(Request $request)
    {
        $marchant = $request->user()->marchant;

        if (!$marchant) {
            return $this->errorResponse('Marchant non trouvé', 404);
        }

        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();

        $transactionsToday = $marchant->transactions()
            ->whereDate('created_at', $today)
            ->count();

        $totalAmountToday = $marchant->transactions()
            ->whereDate('created_at', $today)
            ->sum('montant');

        $transactionsThisMonth = $marchant->transactions()
            ->whereDate('created_at', '>=', $thisMonth)
            ->count();

        $totalAmountThisMonth = $marchant->transactions()
            ->whereDate('created_at', '>=', $thisMonth)
            ->sum('montant');

        return $this->successResponse([
            'marchant' => new MarchantResource($marchant),
            'transactions_today' => $transactionsToday,
            'total_amount_today' => $totalAmountToday,
            'transactions_this_month' => $transactionsThisMonth,
            'total_amount_this_month' => $totalAmountThisMonth,
        ], 'Tableau de bord');
    }

    /**
     * @OA\Post(
     *     path="/marchants",
     *     summary="Créer un nouveau marchant",
     *     tags={"Marchants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom_boutique","adresse","ville","telephone_service"},
     *             @OA\Property(property="nom_boutique", type="string", example="Boulangerie Diop"),
     *             @OA\Property(property="adresse", type="string", example="Rue 12, Dakar Plateau"),
     *             @OA\Property(property="ville", type="string", example="Dakar"),
     *             @OA\Property(property="telephone_service", type="string", example="+221781234567"),
     *             @OA\Property(property="email_service", type="string", example="contact@boulangeriediop.sn")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Marchant créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Marchant créé avec succès"),
     *             @OA\Property(property="data", ref="#/components/schemas/Marchant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation")
     *         )
     *     )
     * )
     */
    public function store(CreateMarchantRequest $request)
    {
        $marchant = Marchant::create([
            'user_id' => $request->user()->id,
            'nom_boutique' => $request->nom_boutique,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'telephone_service' => $request->telephone_service,
            'email_service' => $request->email_service,
            'date_activation' => now(),
        ]);

        return $this->successResponse(
            new MarchantResource($marchant),
            'Marchant créé avec succès',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Marchant $marchant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marchant $marchant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marchant $marchant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marchant $marchant)
    {
        //
    }
}
