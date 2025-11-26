<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTransactionsByCompteRequest extends FormRequest
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
            'compte_id' => 'required|string|uuid|exists:comptes,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
            'type' => 'nullable|string|in:depot,retrait,transfert,achat',
            'date_from' => 'nullable|date|before_or_equal:date_to',
            'date_to' => 'nullable|date|after_or_equal:date_from',
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
            'compte_id.required' => 'L\'ID du compte est obligatoire.',
            'compte_id.uuid' => 'L\'ID du compte doit être un UUID valide.',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas.',
            'limit.integer' => 'La limite doit être un nombre entier.',
            'limit.min' => 'La limite doit être d\'au moins 1.',
            'limit.max' => 'La limite ne peut pas dépasser 100.',
            'offset.integer' => 'Le décalage doit être un nombre entier.',
            'offset.min' => 'Le décalage doit être positif ou nul.',
            'type.in' => 'Le type doit être l\'un des suivants : depot, retrait, transfert, achat.',
            'date_from.date' => 'La date de début doit être une date valide.',
            'date_from.before_or_equal' => 'La date de début doit être antérieure ou égale à la date de fin.',
            'date_to.date' => 'La date de fin doit être une date valide.',
            'date_to.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
