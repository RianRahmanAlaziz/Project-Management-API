<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceMember\InviteWorkspaceMemberRequest;
use App\Http\Requests\WorkspaceMember\UpdateWorkspaceMemberRoleRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\WorkspaceMemberResource;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\WorkspaceMemberService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkspaceMemberController extends Controller
{
    public function __construct(
        private readonly WorkspaceMemberService $workspaceMemberService,
    ) {}

    public function index(
        Request $request,
        Workspace $workspace,
    ): JsonResponse {
        Gate::authorize('viewAny', $workspace);

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100,
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $members = $this->workspaceMemberService
            ->paginateForWorkspace(
                workspace: $workspace,
                perPage: $perPage,
                search: $search,
            );

        return ApiResponse::success(
            data: WorkspaceMemberResource::collection(
                $members->getCollection()
            )->resolve($request),
            message: 'Daftar member workspace berhasil diambil',
            meta: [
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                    'from' => $members->firstItem(),
                    'to' => $members->lastItem(),
                ],
            ],
        );
    }

    public function availableMembers(
        Request $request,
        Workspace $workspace,
    ): JsonResponse {
        Gate::authorize('create', $workspace);

        $perPage = min(
            max(
                $request->integer('per_page', 15),
                1,
            ),
            100,
        );

        $search = trim(
            (string) $request->query(
                'search',
                '',
            ),
        );

        $users = $this->workspaceMemberService
            ->paginateAvailableMembers(
                workspace: $workspace,
                perPage: $perPage,
                search: $search,
            );

        return ApiResponse::success(
            data: UserResource::collection(
                $users->getCollection(),
            )->resolve($request),

            message: 'Available workspace members retrieved successfully.',

            meta: [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
            ],
        );
    }

    /**
     * Menambahkan member ke workspace.
     */
    public function store(
        InviteWorkspaceMemberRequest $request,
        Workspace $workspace,
    ): JsonResponse {
        $member = $this->workspaceMemberService->create(
            workspace: $workspace,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: WorkspaceMemberResource::make($member)
                ->resolve($request),
            message: 'Member berhasil ditambahkan ke workspace',
            status: 201,
        );
    }

    /**
     * Menampilkan detail member workspace.
     */
    public function show(
        Request $request,
        Workspace $workspace,
        WorkspaceMember $member,
    ): JsonResponse {
        Gate::authorize('viewAny', $workspace);

        $members = $this->workspaceMemberService
            ->loadForResponse($member);

        return ApiResponse::success(
            data: WorkspaceMemberResource::make($members)
                ->resolve($request),
            message: 'Detail member workspace berhasil diambil',
        );
    }

    /**
     * Memperbarui role member workspace.
     */
    public function update(
        UpdateWorkspaceMemberRoleRequest $request,
        Workspace $workspace,
        WorkspaceMember $member,
    ): JsonResponse {
        $member = $this->workspaceMemberService->update(
            member: $member,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: WorkspaceMemberResource::make($member)
                ->resolve($request),
            message: 'Role member berhasil diperbarui',
        );
    }

    /**
     * Menghapus member dari workspace.
     */
    public function destroy(
        Workspace $workspace,
        WorkspaceMember $workspaceMember,
    ): JsonResponse {
        Gate::authorize('delete', $workspace);

        $this->workspaceMemberService->delete(
            $workspaceMember,
        );

        return ApiResponse::success(
            data: null,
            message: 'Member berhasil dihapus dari workspace',
        );
    }
}
