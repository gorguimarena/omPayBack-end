<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RetraitRequest extends FormRequest
{

    use ResponseApi;
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
            'compte_id' => 'required|uuid|exists:comptes,id',
            'partenaire_id' => 'required|uuid|exists:service_partenaires,id',
            'montant' => 'required|numeric|min:100|max:500000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'compte_id.required' => Messages::COMPTE_ID_REQUIRED->value,
            'compte_id.uuid' => Messages::COMPTE_ID_UUID->value,
            'compte_id.exists' => Messages::COMPTE_ID_EXISTS->value,
            'partenaire_id.required' => Messages::PARTENAIRE_ID_REQUIRED->value,
            'partenaire_id.uuid' => Messages::PARTENAIRE_ID_UUID->value,
            'partenaire_id.exists' => Messages::PARTENAIRE_ID_EXISTS->value,
            'montant.required' => Messages::MONTANT_REQUIRED->value,
            'montant.numeric' => Messages::MONTANT_NUMERIC->value,
            'montant.min' => Messages::MONTANT_MIN_100->value,
            'montant.max' => Messages::MONTANT_MAX_100000->value,
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(Messages::INVALIDE_DATA->value, 422, $validator->errors()->toArray())
        );
    }
}
