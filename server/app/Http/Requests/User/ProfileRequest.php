<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
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
          'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
          'username' => 'sometimes|string|unique:users',
          'email'=> 'sometimes|email|unique:users',
          'password'=> 'sometimes|string|min:6',
        ];
    }
}
