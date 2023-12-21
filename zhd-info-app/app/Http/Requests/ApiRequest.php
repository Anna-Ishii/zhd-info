<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if (request()->expectsJson()) {
            $errors['status'] = 'error';
            $errors['errors'] = $validator->errors()->toArray();
            $errors['errorMessages'] = $validator->errors()->all();

            throw new HttpResponseException(
                response()->json($errors, 422)
            );
        }
    }
}