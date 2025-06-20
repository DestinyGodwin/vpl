<?php

namespace App\Http\Requests\v1\products;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'images' => ['sometimes', 'array', 'min:1'],
            'images.*' => ['required_with:images', 'image', 'mimes:jpeg,jpg,png,gif,webp,avif,heic', 'max:2048'],
            'image_ids_to_delete' => ['sometimes', 'array'],
            'image_ids_to_delete.*' => ['uuid', 'exists:product_images,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('category_id')) {
                $category = Category::find($this->category_id);

                if (!$category) {
                    $validator->errors()->add('category_id', 'Invalid category.');
                    return;
                }

                $store = Auth::user()->stores()->where('type', $category->store_type)->first();

                if (!$store) {
                    $validator->errors()->add('store', 'You do not have a store for this category type.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'images.array' => 'Images must be uploaded as an array of files.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Each image must be a jpeg, jpg, png, gif, or webp file.',
            'images.*.max' => 'Each image must not be larger than 2MB.',
            'image_ids_to_delete.*.exists' => 'One or more selected images to delete do not exist.',
        ];
    }
}
