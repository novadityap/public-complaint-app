<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
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
          'username' => [
            'sometimes', 
            'required',
            'string',
            Rule::unique('users')->ignore($this->route('user')->id)
          ],
          'email'=> [
            'sometimes',
            'required',
            'email',
            Rule::unique('users')->ignore($this->route('user')->id)
          ],
          'password'=> 'sometimes|string|min:6',
        ];
    }
}
