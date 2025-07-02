<?php

namespace App\Base;

use Illuminate\Foundation\Http\FormRequest as FormRequestBase;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class FormRequest extends FormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator The validator instance containing validation errors.
     * @throws HttpResponseException Throws an exception with a JSON response containing error details.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'data' => null,
            'message' => 'Invalid data',
            'time' => time(),
            'errors' => $validator->errors(),
            'success' => true
        ], 422));
    }
}
