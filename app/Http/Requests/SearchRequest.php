<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'min:2',
                'max:50'
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:20'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Поисковый запрос обязателен',
            'q.min' => 'Запрос должен содержать минимум 2 символа',
            'q.max' => 'Запрос должен содержать максимум 50 символов',
            'limit.min' => 'Лимит должен быть больше 0',
            'limit.max' => 'Лимит не должен превышать 20'
        ];
    }
}
