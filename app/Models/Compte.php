<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model implements Authenticatable
{
    /** @use HasFactory<\Database\Factories\CompteFactory> */
    use HasFactory, HasUuids, \Illuminate\Auth\Authenticatable;

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
        'pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'pin' => 'hashed',
        ];
    }

    protected $hidden = [
        'pin'
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'telephone';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->telephone;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->pin;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        // Not implemented for comptes
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return null;
    }

    protected function solde(): Attribute
    {
        return Attribute::make(
            get: function () {
                $debits = $this->sentTransactions()
                    ->whereIn('type', ['retrait', 'transfert', 'achat'])
                    ->sum('montant');

                $credits = $this->sentTransactions()
                    ->whereIn('type', ['depot'])
                    ->sum('montant');

                $receivedCredits = $this->receivedTransactions()
                    ->whereIn('type', ['transfert'])
                    ->sum('montant');

                return $credits + $receivedCredits - $debits;
            }
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_compte_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_client_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopeWhereTelephone($query, $telephone)
    {
        return $query->where('telephone', $telephone);
    }
}
