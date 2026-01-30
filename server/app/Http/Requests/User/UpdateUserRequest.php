<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
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

  protected function prepareForValidation(): void
  {
    if ($this->has('roleId')) {
      $this->merge([
        'role_id' => $this->input('roleId')
      ]);
    }

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
      'username' => 'sometimes|required|string|min:3|max:50|unique:users,username,' . $this->route('user')->id,
      'email' => 'sometimes|required|email|max:100|unique:users,email,' . $this->route('user')->id,
      'password' => 'sometimes|required|string|min:6',
      'role_id' => 'sometimes|uuid|exists:roles,id'
    ];
  }
}
