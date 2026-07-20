<?php

namespace App\Http\Requests\ProjectMember;

use App\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && ($this->user()?->can(
                'update',
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
            'role' => [
                'required',
                'string',
                Rule::in([
                    'admin',
                    'member',
                    'viewer',
                ]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih.',
            'role.string' => 'Role tidak valid.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ];
    }
}
