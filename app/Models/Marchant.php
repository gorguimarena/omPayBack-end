<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marchant extends Model
{
    /** @use HasFactory<\Database\Factories\MarchantFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code_merchant',
        'nom_boutique',
        'adresse',
        'ville',
        'url_qr',
        'telephone_service',
        'email_service',
        'date_activation',
    ];

    protected $casts = [
        'date_activation' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'receiver_marchant_id');
    }

    public function scopeWhereCode($query, $code)
    {
        return $query->where('code_merchant', $code);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('date_activation');
    }
}
