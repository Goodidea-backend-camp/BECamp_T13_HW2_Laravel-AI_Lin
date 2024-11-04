<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'email' => ['required',  'email'],
            'password' => ['required', 'string'],
            'self_profile' => ['required', 'string'],
        ];
    }

    // 自定義錯誤訊息
    protected function failedValidation(Validator $validator)
    {
        $errorMessage = implode(' ', $validator->errors()->all());

        $response = $this->error($errorMessage, Response::HTTP_UNPROCESSABLE_ENTITY);
        throw new HttpResponseException($response);
    }
}
