<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sender_compte_id',
        'receiver_client_id',
        'receiver_partenaire_id',
        'receiver_marchant_id',
        'montant',
        'type',
        'status',
        'agent_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'type' => 'string',
            'status' => 'string',
        ];
    }

    public function senderCompte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'sender_compte_id');
    }

    public function destinataireCompte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'receiver_client_id');
    }

    public function destinatairePartenaire(): BelongsTo
    {
        return $this->belongsTo(ServicePartenaire::class, 'receiver_partenaire_id');
    }

    public function destinataireMarchant(): BelongsTo
    {
        return $this->belongsTo(Marchant::class, 'receiver_marchant_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    /**
     * Scope to get transactions for a specific user (client or admin)
     */
    public function scopeForUser($query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isClient()) {
            $client = $user->client;
            $compteIds = $client->comptes->pluck('id');

            return $query->where(function ($query) use ($compteIds) {
                $query->whereIn('sender_compte_id', $compteIds)
                      ->orWhereIn('receiver_client_id', $compteIds)
                      ->orWhereIn('receiver_partenaire_id', $compteIds)
                      ->orWhereNotNull('receiver_marchant_id');
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
