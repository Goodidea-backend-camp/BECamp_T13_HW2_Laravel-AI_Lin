<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class SetupGoogleUserRequest extends FormRequest
{
    use ApiResponse;

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
            'self_profile' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessage = implode(' ', $validator->errors()->all());

        $jsonResponse = $this->error($errorMessage, Response::HTTP_UNPROCESSABLE_ENTITY);
        throw new HttpResponseException($jsonResponse);
    }
}
