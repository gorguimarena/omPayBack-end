<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateMarchantRequest extends FormRequest
{
    use ResponseApi;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorisé pour les utilisateurs authentifiés
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom_boutique' => 'required|string|max:255|unique:marchants,nom_boutique',
            'adresse' => 'required|string|max:500',
            'ville' => 'required|string|max:100',
            'telephone_service' => 'required|string|regex:/^\+221[0-9]{9}$/|unique:marchants,telephone_service',
            'email_service' => 'nullable|email|max:255|unique:marchants,email_service',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nom_boutique.required' => Messages::MARCHANT_NOM_BOUTIQUE_REQUIRED->value,
            'nom_boutique.string' => Messages::MARCHANT_NOM_BOUTIQUE_STRING->value,
            'nom_boutique.max' => Messages::MARCHANT_NOM_BOUTIQUE_MAX_255->value,
            'nom_boutique.unique' => Messages::MARCHANT_NOM_BOUTIQUE_UNIQUE->value,
            'adresse.required' => Messages::MARCHANT_ADRESSE_REQUIRED->value,
            'adresse.string' => Messages::MARCHANT_ADRESSE_STRING->value,
            'adresse.max' => Messages::MARCHANT_ADRESSE_MAX_500->value,
            'ville.required' => Messages::MARCHANT_VILLE_REQUIRED->value,
            'ville.string' => Messages::MARCHANT_VILLE_STRING->value,
            'ville.max' => Messages::MARCHANT_VILLE_MAX_100->value,
            'telephone_service.required' => Messages::MARCHANT_TELEPHONE_SERVICE_REQUIRED->value,
            'telephone_service.string' => Messages::TELEPHONE_STRING->value,
            'telephone_service.regex' => Messages::TELEPHONE_REGEX->value,
            'telephone_service.unique' => Messages::TELEPHONE_UNIQUE->value,
            'email_service.email' => Messages::MARCHANT_EMAIL_SERVICE_EMAIL->value,
            'email_service.max' => Messages::MARCHANT_EMAIL_SERVICE_MAX_255->value,
            'email_service.unique' => Messages::MARCHANT_EMAIL_SERVICE_UNIQUE->value,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nom_boutique' => 'nom de la boutique',
            'adresse' => 'adresse',
            'ville' => 'ville',
            'telephone_service' => 'numéro de téléphone',
            'email_service' => 'adresse email',
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(Messages::INVALIDE_DATA->value, 422, $validator->errors()->toArray())
        );
    }
}
