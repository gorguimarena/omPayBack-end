<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Représentation d'un compte utilisateur",
 *     @OA\Property(property="id", type="string", format="uuid", example="db0f5a30-f7a8-4036-a2c1-5f6b0b1118c9"),
 *     @OA\Property(property="telephone", type="string", example="+221776525959"),
 *     @OA\Property(property="solde", type="number", format="float", example=224900.00),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CompteResource extends JsonResource
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
            'telephone' => $this->telephone,
            'solde' => $this->solde,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}