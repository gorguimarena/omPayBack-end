<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    /** @use HasFactory<\Database\Factories\CompteFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'telephone',
    ];

    protected function solde(): Attribute
    {
        return Attribute::make(
            get: function () {
                $debits = $this->sentTransactions()->where('type', 'debit')->sum('montant');
                $credits = $this->receivedTransactions()->where('type', 'credit')->sum('montant');
                return $credits - $debits;
            }
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
