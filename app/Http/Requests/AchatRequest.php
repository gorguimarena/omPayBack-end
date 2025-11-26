<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AchatRequest extends FormRequest
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
            'sender_compte_id' => 'required|uuid|exists:comptes,id',
            'code_marchant' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^MCH-\d{6}$/', $value)) {
                        return;
                    }

                    if (preg_match('/^\+221[0-9]{9}$/', $value)) {
                        return; 
                    }

                    $fail('Le code marchand doit être au format MCH-XXXXXX ou un numéro de téléphone au format +221XXXXXXXXX.');
                },
            ],
            'montant' => 'required|numeric|min:0.01|max:1000000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sender_compte_id.required' => Messages::ACHAT_SENDER_COMPTE_ID_REQUIRED->value,
            'sender_compte_id.uuid' => Messages::COMPTE_ID_UUID->value,
            'sender_compte_id.exists' => Messages::ACHAT_SENDER_COMPTE_ID_EXISTS->value,
            'code_marchant.required' => Messages::ACHAT_CODE_MARCHANT_REQUIRED->value,
            'code_marchant.regex' => Messages::ACHAT_CODE_MARCHANT_REGEX->value,
            'montant.required' => Messages::ACHAT_MONTANT_REQUIRED->value,
            'montant.numeric' => Messages::MONTANT_NUMERIC->value,
            'montant.min' => Messages::ACHAT_MONTANT_MIN->value,
            'montant.max' => Messages::ACHAT_MONTANT_MAX_1000000->value,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sender_compte_id' => 'compte expéditeur',
            'code_marchant' => 'code marchand',
            'montant' => 'montant',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(Messages::INVALIDE_DATA->value, 422, $validator->errors()->toArray())
        );
    }
}
