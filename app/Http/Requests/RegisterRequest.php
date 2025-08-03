<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'username' => [
                'required',
                'string',
                'min:3',
                'max:16',
                'unique:authme,username',
                'regex:/^[a-zA-Z0-9_]+$/'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:64',
                'confirmed'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:authme,email'
            ],
            'realname' => [
                'nullable',
                'string',
                'max:255'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Имя пользователя обязательно для заполнения',
            'username.min' => 'Имя пользователя должно содержать минимум 3 символа',
            'username.max' => 'Имя пользователя должно содержать максимум 16 символов',
            'username.unique' => 'Пользователь с таким именем уже существует',
            'username.regex' => 'Имя пользователя может содержать только латинские буквы, цифры и подчеркивания',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Подтверждение пароля не совпадает',
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Пользователь с таким email уже существует'
        ];
    }


    public function prepareForValidation(): void
    {
        if($this->has('username')) {
            $this->merge([
                'username' => strtolower($this->input('username')),
            ]);
        }
    }
}
