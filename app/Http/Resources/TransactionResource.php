<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Représentation d'une transaction",
 *     @OA\Property(property="id", type="string", format="uuid", example="019a7823-4c40-71c5-8c60-e8e9e5575ccc"),
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait", "transfert", "achat"}, example="achat"),
 *     @OA\Property(property="montant", type="number", format="float", example=500.00),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}, example="completed"),
 *     @OA\Property(property="sender_compte", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9"),
 *         @OA\Property(property="telephone", type="string", example="+221776525959"),
 *         @OA\Property(property="solde", type="number", format="float", example=224900.00)
 *     ),
 *     @OA\Property(property="receiver_compte", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9"),
 *         @OA\Property(property="telephone", type="string", example="+221776525959"),
 *         @OA\Property(property="solde", type="number", format="float", example=224900.00)
 *     ),
 *     @OA\Property(property="receiver_partenaire", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid", example="37848294-ef0b-4eb6-9a5b-bb7708da27a3"),
 *         @OA\Property(property="nom", type="string", example="Senelec")
 *     ),
 *     @OA\Property(property="receiver_marchant", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="code_merchant", type="string", example="MCH-123456"),
 *         @OA\Property(property="nom_boutique", type="string", example="Boutique Test OM Pay")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'montant' => $this->montant,
            'status' => $this->status,
            'sender_compte' => $this->whenLoaded('senderCompte', function () {
                return [
                    'id' => $this->senderCompte->id,
                    'telephone' => $this->senderCompte->telephone,
                    'solde' => $this->senderCompte->solde,
                ];
            }),
            'receiver_compte' => $this->whenLoaded('destinataireCompte', function () {
                return [
                    'id' => $this->destinataireCompte->id,
                    'telephone' => $this->destinataireCompte->telephone,
                    'solde' => $this->destinataireCompte->solde,
                ];
            }),
            'receiver_partenaire' => $this->when($this->receiver_partenaire_id, function () {
                $partenaire = $this->resource->receiverPartenaire;
                return $partenaire ? [
                    'id' => $partenaire->id,
                    'nom' => $partenaire->nom,
                ] : null;
            }),
            'receiver_marchant' => $this->when($this->receiver_marchant_id, function () {
                $marchant = $this->resource->destinataireMarchant;
                return $marchant ? [
                    'id' => $marchant->id,
                    'code_merchant' => $marchant->code_merchant,
                    'nom_boutique' => $marchant->nom_boutique,
                ] : null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
