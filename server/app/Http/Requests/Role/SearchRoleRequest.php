<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class SearchRoleRequest extends FormRequest
{
  public function prepareForValidation()
  {
    $this->merge([
      'q' => $this->input('q', null),
      'page' => (int) $this->input('page', 1),
      'limit' => (int) $this->input('limit', 10),
      'sortBy' => $this->input('sortBy', 'created_at'),
      'sortOrder' => $this->input('sortOrder', 'desc'),
    ]);
  }

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
      'page' => 'sometimes|integer|min:1',
      'limit' => 'sometimes|integer|min:1|max:100',
      'q' => 'nullable|string',
      'sortBy' => 'sometimes|string',
      'sortOrder' => 'sometimes|string|in:asc,desc',
    ];
  }
}
