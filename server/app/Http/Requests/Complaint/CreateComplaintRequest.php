<?php

namespace App\Http\Requests\Complaint;

use Illuminate\Foundation\Http\FormRequest;

class CreateComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'title' => 'required|string',
          'content' => 'required|string',
          'status' => 'required|in:pending,in_progress,resolved',
          'category_id' => 'required|uuid|exists:categories',
          'images' => 'sometimes|array|max:5',
          'images.*' => 'file|image|mimes:jpeg,png,jpg|max:2048',
         ];
    }
}
