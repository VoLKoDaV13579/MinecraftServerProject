<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:64',
                'confirmed',
                'different:current_password'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Текущий пароль обязателен',
            'password.required' => 'Новый пароль обязателен',
            'password.min' => 'Новый пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Подтверждение нового пароля не совпадает',
            'password.different' => 'Новый пароль должен отличаться от текущего'
        ];
    }
}
