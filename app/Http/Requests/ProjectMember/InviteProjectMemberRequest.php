<?php

namespace App\Http\Requests\ProjectMember;

use App\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InviteProjectMemberRequest extends FormRequest
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
            'user_id' => [
                'required',
                'integer',

                Rule::exists('workspace_members', 'user_id')
                    ->where(
                        'workspace_id',
                        $project?->workspace_id,
                    ),
            ],

            'role' => [
                'required',
                'string',
                'max:30',

                Rule::in([
                    'admin',
                    'member',
                    'viewer',
                ]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => (int) $this->input('user_id'),
        ]);
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User wajib dipilih.',
            'user_id.integer' => 'User tidak valid.',
            'user_id.exists' => 'User bukan anggota workspace ini.',

            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
        ];
    }
}
