<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePartenaire extends Model
{
    /** @use HasFactory<\Database\Factories\ServicePartenaireFactory> */
    use HasFactory, HasUlids;

    public function client() : BelongsTo {
        return $this->belongsTo(Client::class);
    }
}
