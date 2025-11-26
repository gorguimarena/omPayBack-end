<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePartenaire extends Model
{
    /** @use HasFactory<\Database\Factories\ServicePartenaireFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'api_url',
        'api_key'
    ];
}
