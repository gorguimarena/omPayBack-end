<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    /** @use HasFactory<\Database\Factories\CompteFactory> */
    use HasFactory, HasUlids;

    protected function solde(): Attribute {
        return Attribute::make(
            get: fn() => null
        );
    }

    public function transactions() : HasMany {
        return $this->hasMany(Transaction::class);
    }
}
