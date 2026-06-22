<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Resources\V1\WorkspaceResource;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class WorkspaceController extends Controller
{
    public function __construct(
        private readonly WorkspaceService $workspaceService,
    ) {}

    /**
     * Menampilkan workspace milik user login.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Workspace::class);

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100,
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $workspaces = $this->workspaceService
            ->paginateForUser(
                user: $request->user(),
                perPage: $perPage,
                search: $search,
            );

        return ApiResponse::success(
            data: WorkspaceResource::collection(
                $workspaces->getCollection()
            )->resolve($request),
            message: 'Workspace berhasil diambil',
            meta: [
                'pagination' => [
                    'current_page' => $workspaces->currentPage(),
                    'last_page' => $workspaces->lastPage(),
                    'per_page' => $workspaces->perPage(),
                    'total' => $workspaces->total(),
                    'from' => $workspaces->firstItem(),
                    'to' => $workspaces->lastItem(),
                ],
            ],
        );
    }

    /**
     * Membuat workspace baru.
     */
    public function store(
        StoreWorkspaceRequest $request
    ): JsonResponse {
        $workspace = $this->workspaceService->create(
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: WorkspaceResource::make($workspace)
                ->resolve($request),
            message: 'Workspace berhasil dibuat',
            status: 201,
        );
    }

    /**
     * Menampilkan detail workspace.
     */
    public function show(
        Request $request,
        Workspace $workspace,
    ): JsonResponse {
        Gate::authorize('view', $workspace);

        $workspace = $this->workspaceService
            ->loadForResponse(
                workspace: $workspace,
                user: $request->user(),
            );

        return ApiResponse::success(
            data: WorkspaceResource::make($workspace)
                ->resolve($request),
            message: 'Detail workspace berhasil diambil',
        );
    }

    /**
     * Memperbarui workspace.
     */
    public function update(
        UpdateWorkspaceRequest $request,
        Workspace $workspace,
    ): JsonResponse {
        $workspace = $this->workspaceService->update(
            workspace: $workspace,
            user: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: WorkspaceResource::make($workspace)
                ->resolve($request),
            message: 'Workspace berhasil diperbarui',
        );
    }

    /**
     * Menghapus workspace.
     */
    public function destroy(
        Workspace $workspace
    ): JsonResponse {
        Gate::authorize('delete', $workspace);

        $this->workspaceService->delete($workspace);

        return ApiResponse::success(
            data: null,
            message: 'Workspace berhasil dihapus',
        );
    }
}
