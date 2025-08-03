<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'realname' => [
                'nullable',
                'string',
                'max:255'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                "unique:authme,email,{$userId},id"
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'max:64',
                'confirmed'
            ],
            'current_password' => [
                'required_with:password',
                'string'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Подтверждение пароля не совпадает',
            'current_password.required_with' => 'Для смены пароля необходимо указать текущий пароль'
        ];
    }
}
