<?php

namespace App\Http\Requests\v1\products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequestRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'required_with:images|image|mimes:jpeg,jpg,png,gif,webp,avif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'images.array' => 'Images must be an array.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be jpeg, jpg, png, gif, webp or avif.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
