<?php

namespace App\Providers;

use App\Models\Compte;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

// class CompteUserProvider implements UserProvider
// {
//     public function retrieveById($identifier)
//     {
//         return Compte::find($identifier);
//     }

//     public function retrieveByToken($identifier, $token)
//     {
//         return null; // pas utilisé ici
//     }

//     public function updateRememberToken(Authenticatable $user, $token)
//     {
//         // pas nécessaire pour ton cas
//     }

//     public function retrieveByCredentials(array $credentials)
//     {
//         $telephone = $credentials['telephone'] ?? null;
//         $pin = $credentials['pin'] ?? null;

//         if (!$telephone || !$pin) {
//             return null;
//         }

//         return Compte::where('telephone', $telephone)->first();
//     }

//     public function validateCredentials(Authenticatable $user, array $credentials)
//     {
//         return Hash::check($credentials['pin'], $user->pin);
//     }

//     public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
//     {
//         // Pour les PINs, on ne fait pas de rehash automatique
//         return false;
//     }
// }
