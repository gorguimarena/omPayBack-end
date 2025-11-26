<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTransactionRequest extends FormRequest
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
        $rules = [];

        $type = $this->input('type');

        switch ($type) {
            case 'depot':
                $rules = [
                    'type' => 'required|in:depot',
                    'compte_id' => 'required|uuid|exists:comptes,id',
                    'montant' => 'required|numeric|min:100|max:1000000',
                ];
                break;

            case 'retrait':
                $rules = [
                    'type' => 'required|in:retrait',
                    'compte_id' => 'required|uuid|exists:comptes,id',
                    'partenaire_id' => 'required|uuid|exists:service_partenaires,id',
                    'montant' => 'required|numeric|min:100|max:500000',
                ];
                break;

            case 'transfert':
                $rules = [
                    'type' => 'required|in:transfert',
                    'sender_compte_id' => 'required|uuid|exists:comptes,id',
                    'receiver_compte_id' => 'required|uuid|exists:comptes,id|different:sender_compte_id',
                    'montant' => 'required|numeric|min:100|max:100000',
                ];
                break;

            default:
                $rules = [
                    'type' => 'required|in:depot,retrait,transfert',
                ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => Messages::TRANSACTION_TYPE_REQUIRED->value,
            'type.in' => Messages::TRANSACTION_TYPE_IN->value,
            'compte_id.required' => Messages::COMPTE_ID_REQUIRED->value,
            'compte_id.exists' => Messages::COMPTE_ID_EXISTS->value,
            'partenaire_id.required' => Messages::TRANSACTION_PARTENAIRE_ID_REQUIRED->value,
            'partenaire_id.exists' => Messages::PARTENAIRE_ID_EXISTS->value,
            'sender_compte_id.required' => Messages::TRANSACTION_SENDER_COMPTE_ID_REQUIRED->value,
            'sender_compte_id.exists' => Messages::COMPTE_ID_EXISTS->value,
            'receiver_compte_id.required' => Messages::TRANSACTION_RECEIVER_COMPTE_ID_REQUIRED->value,
            'receiver_compte_id.exists' => Messages::COMPTE_ID_EXISTS->value,
            'receiver_compte_id.different' => Messages::RECEIVER_COMPTE_ID_DIFFERENT->value,
            'montant.required' => Messages::MONTANT_REQUIRED->value,
            'montant.numeric' => Messages::MONTANT_NUMERIC->value,
            'montant.min' => Messages::TRANSACTION_MONTANT_MIN_100_XOF->value,
            'montant.max' => Messages::TRANSACTION_MONTANT_MAX_EXCEEDED->value,
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(Messages::INVALIDE_DATA->value, 422, $validator->errors()->toArray())
        );
    }
}
