<?php

namespace App\Http\Requests;

use App\Enums\Messages;
use App\ResponseApi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAccountRequest extends FormRequest
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
            'telephone' => 'required|string|regex:/^\+221[0-9]{9}$/|unique:comptes,telephone',
            'pin' => 'required|string|digits:4|confirmed',
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
            'telephone.unique' => Messages::TELEPHONE_UNIQUE->value,
            'pin.required' => Messages::PIN_REQUIRED->value,
            'pin.string' => Messages::PIN_STRING->value,
            'pin.digits' => Messages::PIN_DIGITS->value,
            'pin.confirmed' => Messages::PIN_CONFIRMED->value,
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
