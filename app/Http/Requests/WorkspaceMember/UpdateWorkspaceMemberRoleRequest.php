<?php

namespace App\Http\Requests\WorkspaceMember;

use App\Enums\WorkspaceRole;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateWorkspaceMemberRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'sometimes',
                'required',
                new Enum(WorkspaceRole::class),
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
        ];
    }
}
