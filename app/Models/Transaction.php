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
        'montant',
        'type',
        'status',
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
}
