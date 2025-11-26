<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SoldeAccountRequest extends FormRequest
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
