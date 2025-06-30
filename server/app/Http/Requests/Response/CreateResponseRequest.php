<?php

namespace App\Http\Requests\Response;

use Illuminate\Foundation\Http\FormRequest;

class CreateResponseRequest extends FormRequest
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
      'message' => 'required|string',
      'status' => 'required|in:pending,in_progress,resolved',
    ];
  }
}
