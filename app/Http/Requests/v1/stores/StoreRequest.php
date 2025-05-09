<?php

namespace App\Http\Requests\v1\stores;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'type' => ['required', 'in:regular,food'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:2048'],
            'status' => ['nullable', 'in:is_active,is_inactive'],
        ];
    }
}
