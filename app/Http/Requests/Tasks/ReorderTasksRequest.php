<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tasks' => [
                'required',
                'array',
                'min:1',
            ],

            'tasks.*.id' => [
                'required',
                'integer',
                'distinct',
            ],

            'tasks.*.position' => [
                'required',
                'integer',
                'min:1',
            ],

            'tasks.*.column_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
