<?php

namespace App\Http\Requests\Workspace;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferWorkspaceOwnershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');

        return $workspace instanceof Workspace
            && ($this->user()?->can(
                'transferOwnership',
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
        $workspace = $this->route('workspace');

        return [
            'user_id' => [
                'required',
                'integer',

                Rule::exists('workspace_members', 'user_id')
                    ->where(
                        fn($query) => $query->where(
                            'workspace_id',
                            $workspace?->id,
                        )
                    ),

                Rule::notIn([
                    $workspace?->owner_id,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Owner baru wajib dipilih.',
            'user_id.integer' => 'User tidak valid.',
            'user_id.exists' => 'User harus menjadi member workspace.',
            'user_id.not_in' => 'User tersebut sudah menjadi owner workspace.',
        ];
    }
}
