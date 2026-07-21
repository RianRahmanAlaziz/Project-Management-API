<?php

namespace App\Http\Requests\KanbanColumn;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreKanbanColumnRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'color' => [
                'nullable',
                'string',
                'max:20',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kolom wajib diisi.',
            'name.string' => 'Nama kolom tidak valid.',
            'name.max' => 'Nama kolom maksimal 100 karakter.',

            'color.string' => 'Warna kolom tidak valid.',
            'color.max' => 'Warna kolom maksimal 20 karakter.',
        ];
    }
}
