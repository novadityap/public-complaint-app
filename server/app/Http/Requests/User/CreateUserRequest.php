<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     protected function prepareForValidation(): void {
      $this->merge([
        'role_id' => $this->input('roleId')
      ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'username' => 'required|string|unique:users',
          'email'=> 'required|email|unique:users',
          'password'=> 'required|string|min:6',
          'role_id' => 'required|uuid|exists:roles,id'
        ];
    }
}
