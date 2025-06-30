<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
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
    if ($this->has('categoryId')) {
      $this->merge([
        'category_id' => $this->input('categoryId')
      ]);
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
      'title' => 'sometimes|required|string',
      'description' => 'sometimes|required|string',
      'category_id' => 'sometimes|required|uuid|exists:categories,id',
      'images' => 'sometimes|array|max:5',
      'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
    ];
  }

  public function messages(): array
  {
    return [
      'images.*.image' => 'The file must be an image.',
      'images.*.mimes' => 'The image must be in jpg, jpeg, or png format.',
      'images.*.max' => 'The image field must not be greater than 2048 kilobytes.',
      'images.required' => 'You must upload at least one image.',
      'images.array' => 'The image field must be an array of files.',
    ];
  }
}
