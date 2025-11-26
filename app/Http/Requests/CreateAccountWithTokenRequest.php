<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAccountWithTokenRequest extends FormRequest
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
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'pin' => 'required|string|digits:4',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => Messages::TOKEN_REQUIRED->value,
            'token.string' => Messages::TOKEN_STRING->value,
            'name.required' => Messages::NAME_REQUIRED->value,
            'name.string' => 'Le nom doit être une chaîne de caractères',
            'name.max' => Messages::NAME_MAX_255->value,
            'pin.required' => Messages::PIN_REQUIRED->value,
            'pin.string' => Messages::PIN_STRING->value,
            'pin.digits' => Messages::PIN_DIGITS->value,
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
