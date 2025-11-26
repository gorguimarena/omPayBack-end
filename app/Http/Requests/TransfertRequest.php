<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransfertRequest extends FormRequest
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
            'sender_compte_id' => 'required|uuid|exists:comptes,id',
            'receiver_compte_id' => 'required|uuid|exists:comptes,id|different:sender_compte_id',
            'montant' => 'required|numeric|min:100|max:100000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sender_compte_id.required' => Messages::COMPTE_ID_REQUIRED->value,
            'sender_compte_id.uuid' => Messages::COMPTE_ID_UUID->value,
            'sender_compte_id.exists' => Messages::COMPTE_ID_EXISTS->value,
            'receiver_compte_id.required' => Messages::COMPTE_ID_REQUIRED->value,
            'receiver_compte_id.uuid' => Messages::COMPTE_ID_UUID->value,
            'receiver_compte_id.exists' => Messages::RECEIVER_COMPTE_ID_EXISTS->value,
            'receiver_compte_id.different' => Messages::RECEIVER_COMPTE_ID_DIFFERENT->value,
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
