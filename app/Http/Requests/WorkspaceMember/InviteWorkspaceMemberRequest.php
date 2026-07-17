<?php

namespace App\Http\Requests\WorkspaceMember;

use App\Enums\WorkspaceRole;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InviteWorkspaceMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can(
                'create',
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
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'role' => [
                'required',
                new Enum(WorkspaceRole::class),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => (int) $this->input('user_id'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User wajib dipilih.',
            'user_id.integer' => 'User tidak valid.',
            'user_id.exists' => 'User tidak ditemukan.',

            'role.required' => 'Role wajib dipilih.',
        ];
    }
}
