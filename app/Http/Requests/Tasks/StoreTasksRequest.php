<?php

namespace App\Http\Requests\Tasks;


use App\Models\Task;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && (
                $this->user()?->can(
                    'create',
                    [Task::class, $project],
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
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'column_id' => [
                'required',
                'exists:kanban_columns,id',
            ],

            'assignee_id' => [
                'nullable',
                'exists:users,id',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'column_id.required' => 'Kolom Kanban wajib dipilih.',
            'column_id.exists' => 'Kolom Kanban tidak ditemukan.',

            'title.required' => 'Judul task wajib diisi.',
            'title.max' => 'Judul task maksimal 255 karakter.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'assignee_id.exists' => 'Assignee tidak ditemukan.',

            'status.max' => 'Status maksimal 50 karakter.',

            'priority.max' => 'Priority maksimal 50 karakter.',

            'start_date.date' => 'Tanggal mulai tidak valid.',

            'due_date.date' => 'Tanggal selesai tidak valid.',
            'due_date.after_or_equal' =>
            'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}
