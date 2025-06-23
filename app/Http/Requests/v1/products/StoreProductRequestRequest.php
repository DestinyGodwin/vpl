<?php

namespace App\Http\Requests\v1\products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequestRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => ['required', 'exists:categories,id'],
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp,avif,heic|max:2048',
        ];
    }
    
    public function messages(): array
    {
        return [
            'images.required' => 'You must upload at least one image.',
            'images.array' => 'Images must be an array.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be jpeg, jpg, png, gif, webp or avif.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
