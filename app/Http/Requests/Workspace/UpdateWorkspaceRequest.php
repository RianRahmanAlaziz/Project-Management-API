<?php

namespace App\Http\Requests\Workspace;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can(
                'update',
                $workspace,
            ) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'color' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $name = $this->input('name');

            $data['name'] = is_string($name)
                ? trim($name)
                : $name;
        }

        if ($this->has('color')) {
            $color = $this->input('color');

            $data['color'] = is_string($color)
                ? trim($color)
                : $color;
        }

        if ($this->has('description')) {
            $description = $this->input('description');

            $data['description'] = is_string($description)
                ? trim($description)
                : $description;
        }

        $this->merge($data);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama workspace wajib diisi.',
            'name.string' => 'Nama workspace harus berupa teks.',
            'name.max' => 'Nama workspace maksimal 100 karakter.',

            'color.string' => 'color workspace wajib diisi.',
            'color.max' => 'color workspace maksimal 100 karakter.',

            'description.string' => 'Deskripsi workspace harus berupa teks.',
            'description.max' => 'Deskripsi workspace maksimal 2000 karakter.',
        ];
    }
}
