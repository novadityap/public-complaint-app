<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
          'avatar' => 'sometimes|required|file|image|mimes:jpeg,png,jpg|max:2048',
          'username' => 'sometimes|required|string|unique:users',
          'email'=> 'sometimes|required|email|unique:users',
          'password'=> 'sometimes|required|string|min:6',
          'role_id' => 'sometimes|required|uuid|exists:roles'
        ];
    }
}
