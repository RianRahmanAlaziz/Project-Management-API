<?php

namespace App\Http\Requests\KanbanColumn;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderKanbanColumnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && ($this->user()?->can(
                'create',
                $project,
            ) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'columns' => [
                'required',
                'array',
                'min:1',
            ],

            'columns.*' => [
                'required',
                'integer',
                'distinct',

                Rule::exists('kanban_columns', 'id')
                    ->where(
                        'project_id',
                        $project?->id,
                    ),
            ],

            'columns.*.position' => [
                'required',
                'integer',
                'min:0',
                'distinct',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'columns.required' => 'Data kolom wajib dikirim.',
            'columns.array' => 'Data kolom harus berupa array.',
            'columns.min' => 'Minimal satu kolom harus dikirim.',

            'columns.*.id.required' => 'ID kolom wajib diisi.',
            'columns.*.id.integer' => 'ID kolom tidak valid.',
            'columns.*.id.distinct' => 'ID kolom tidak boleh duplikat.',
            'columns.*.id.exists' => 'Kolom tidak ditemukan pada project ini.',

            'columns.*.position.required' => 'Posisi kolom wajib diisi.',
            'columns.*.position.integer' => 'Posisi kolom tidak valid.',
            'columns.*.position.min' => 'Posisi kolom minimal 0.',
            'columns.*.position.distinct'
            => 'Posisi kolom tidak boleh duplikat.',
        ];
    }
}
