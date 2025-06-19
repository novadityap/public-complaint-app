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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'title' => 'sometimes|required|string',
          'content' => 'sometimes|required|string',
          'status' => 'sometimes|required|in:pending,in_progress,resolved',
          'category_id' => 'sometimes|required|uuid|exists:categories',
          'images' => 'sometimes|required|array|max:5',
          'images.*' => 'file|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
