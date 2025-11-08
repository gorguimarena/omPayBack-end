<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, HasUlids;

    public function user() : HasOne {
        return $this->hasOne(User::class);
    }

    public function service_partenaire() : HasOne {
        return $this->hasOne(ServicePartenaire::class);
    }
}
