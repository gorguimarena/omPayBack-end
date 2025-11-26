<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransfertByTelephone extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sender_compte_id' => 'required|string|exists:comptes,id',
            'telephone' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'montant' => 'required|numeric|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sender_compte_id.required' => 'L\'ID du compte expéditeur est obligatoire.',
            'sender_compte_id.exists' => 'Le compte expéditeur n\'existe pas.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format +221XXXXXXXXX.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ];
    }
}
