<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DepotTelephoneRequest extends FormRequest
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
            'telephone' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'montant' => 'required|numeric|min:100|max:1000000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'telephone.required' => Messages::TELEPHONE_REQUIRED->value,
            'telephone.string' => Messages::TELEPHONE_STRING->value,
            'telephone.regex' => Messages::TELEPHONE_REGEX->value,
            'montant.required' => Messages::MONTANT_REQUIRED->value,
            'montant.numeric' => Messages::MONTANT_NUMERIC->value,
            'montant.min' => Messages::MONTANT_MIN_100->value,
            'montant.max' => Messages::MONTANT_MAX_1000000->value,
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
