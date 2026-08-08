<?php

namespace App\Http\Requests\Tasks;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task
            && (
                $this->user()?->can(
                    'update',
                    $task,
                ) ?? false
            );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'column_id' => [
                'sometimes',
                'required',
                'exists:kanban_columns,id',
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'assignee_id' => [
                'sometimes',
                'nullable',
                'exists:users,id',
            ],

            'priority' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'start_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'due_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'column_id.exists' => 'Kolom Kanban tidak ditemukan.',

            'title.required' => 'Judul task wajib diisi.',
            'title.max' => 'Judul task maksimal 255 karakter.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'assignee_id.exists' => 'Assignee tidak ditemukan.',

            'priority.max' => 'Priority maksimal 50 karakter.',

            'start_date.date' => 'Tanggal mulai tidak valid.',

            'due_date.date' => 'Tanggal selesai tidak valid.',
            'due_date.after_or_equal' =>
            'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}
