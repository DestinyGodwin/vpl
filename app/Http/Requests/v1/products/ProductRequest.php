<?php

namespace App\Http\Requests\v1\products;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required','image', 'mimes:jpeg,jpg,png,gif,webp,avif','max:2048' ],         
        ];
    }
    public function messages()
{
    return [
        'images.required' => 'You must upload at least one product image.',
        'images.array' => 'Images must be uploaded as an array of files.',
        'images.*.image' => 'Each file must be a valid image.',
        'images.*.mimes' => 'Each image must be a jpeg, jpg, png, gif, or webp file.',
        'images.*.max' => 'Each image must not be larger than 2MB.',
    ];
}
}
