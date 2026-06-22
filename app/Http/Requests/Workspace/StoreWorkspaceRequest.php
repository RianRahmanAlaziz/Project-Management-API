<?php

namespace App\Http\Requests\Workspace;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkspaceRequest extends FormRequest
{
    /**
     * Policy create akan diperiksa sebelum controller berjalan.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(
            'create',
            Workspace::class,
        ) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $description = $this->input('description');

        $this->merge([
            'name' => is_string($name)
                ? trim($name)
                : $name,

            'description' => is_string($description)
                ? trim($description)
                : $description,
        ]);
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

            'description.string' => 'Deskripsi workspace harus berupa teks.',
            'description.max' => 'Deskripsi workspace maksimal 2000 karakter.',
        ];
    }
}
