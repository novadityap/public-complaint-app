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

  protected function prepareForValidation(): void
  {
    $fields = ['password', 'avatar'];

    foreach ($fields as $field) {
      if ($this->has($field) && ($this->{$field} === null || $this->{$field} === '')) {
        $this->request->remove($field);
      }
    }
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
      'username' => 'sometimes|required|string|min:3|max:50|unique:users,username',
      'email' => 'sometimes|required|email|unique:users,email',
      'password' => 'sometimes|required|string|min:6',
    ];
  }
}
