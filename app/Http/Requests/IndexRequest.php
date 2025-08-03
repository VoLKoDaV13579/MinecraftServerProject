<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50'
            ],
            'username' => [
                'nullable',
                'string',
                'max:16'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'is_online' => [
                'nullable',
                'boolean'
            ],
            'world' => [
                'nullable',
                'string',
                'max:100'
            ],
            'registered_from' => [
                'nullable',
                'date'
            ],
            'registered_to' => [
                'nullable',
                'date',
                'after_or_equal:registered_from'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.min' => 'Количество записей на страницу должно быть больше 0',
            'per_page.max' => 'Количество записей на страницу не должно превышать 50',
            'registered_to.after_or_equal' => 'Дата окончания должна быть не раньше даты начала'
        ];
    }
}
