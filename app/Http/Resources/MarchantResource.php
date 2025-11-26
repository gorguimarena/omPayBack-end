<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Marchant",
 *     type="object",
 *     title="Marchant",
 *     description="Représentation d'un marchant/boutique",
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="user_id", type="integer", example=45),
 *     @OA\Property(property="code_merchant", type="string", example="MCH-000012"),
 *     @OA\Property(property="nom_boutique", type="string", example="Boulangerie Diop"),
 *     @OA\Property(property="adresse", type="string", example="Rue 12, Dakar Plateau"),
 *     @OA\Property(property="ville", type="string", example="Dakar"),
 *     @OA\Property(property="url_qr", type="string", example="https://qr.example.com/MCH-000012"),
 *     @OA\Property(property="telephone_service", type="string", example="+221781234567"),
 *     @OA\Property(property="email_service", type="string", example="contact@boulangeriediop.sn"),
 *     @OA\Property(property="date_activation", type="string", format="date-time", example="2025-01-15T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MarchantResource extends JsonResource
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
            'user_id' => $this->user_id,
            'code_merchant' => $this->code_merchant,
            'nom_boutique' => $this->nom_boutique,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'url_qr' => $this->url_qr,
            'telephone_service' => $this->telephone_service,
            'email_service' => $this->email_service,
            'date_activation' => $this->date_activation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}