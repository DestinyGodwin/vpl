<?php

namespace App\Http\Requests\v1\auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'first_name' => ['required', 'min:3', 'max:255', 'string'],
            'last_name' => ['required', 'min:3', 'max:255', 'string'],
            'phone' => [   'required','regex:/^\+?[0-9]{10,15}$/', 'unique:users,phone'],
            'email' => ['required', 'unique:users,email',],
            'profile_picture' => ['nullable','image','max:2048'],
            'university_id' => ['required', 'exists:universities,id'],
            'password' => ['required', 'string', 'confirmed', 'max:255', 'min:8'],
        ];
    }
}
