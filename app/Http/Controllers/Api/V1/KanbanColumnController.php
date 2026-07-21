<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\KanbanColumn\ReorderKanbanColumnRequest;
use App\Http\Requests\KanbanColumn\StoreKanbanColumnRequest;
use App\Http\Requests\KanbanColumn\UpdateKanbanColumnRequest;
use App\Http\Resources\V1\KanbanColumnResource;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\KanbanColumnService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KanbanColumnController extends Controller
{
    public function __construct(
        private readonly KanbanColumnService $kanbanColumnService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(
        Request $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        Gate::authorize(
            'view',
            $project,
        );

        $columns = $this->kanbanColumnService
            ->getForProject($project);

        return ApiResponse::success(
            data: KanbanColumnResource::collection(
                $columns,
            )->resolve($request),
            message: 'Kanban columns retrieved successfully.',
        );
    }

    /**
     * Store a new kanban column.
     */
    public function store(
        StoreKanbanColumnRequest $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $column = $this->kanbanColumnService->create(
            project: $project,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new KanbanColumnResource($column),
            message: 'Kanban column created successfully.',
            status: 201,
        );
    }

    /**
     * Update a kanban column.
     */
    public function update(
        UpdateKanbanColumnRequest $request,
        Workspace $workspace,
        Project $project,
        KanbanColumn $column,
    ): JsonResponse {
        $column = $this->kanbanColumnService->update(
            column: $column,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new KanbanColumnResource($column),
            message: 'Kanban column updated successfully.',
        );
    }

    /**
     * Reorder kanban columns.
     */
    public function reorder(
        ReorderKanbanColumnRequest $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->kanbanColumnService->reorder(
            project: $project,
            columns: $request->validated('columns'),
        );

        $columns = $this->kanbanColumnService
            ->getForProject($project);

        return ApiResponse::success(
            data: KanbanColumnResource::collection(
                $columns,
            )->resolve($request),

            message: 'Kanban columns reordered successfully.',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Workspace $workspace,
        Project $project,
        KanbanColumn $column,
    ): JsonResponse {
        Gate::authorize(
            'delete',
            $column,
        );

        $this->kanbanColumnService->delete(
            $column,
        );

        return ApiResponse::success(
            data: null,
            message: 'Kanban column deleted successfully.',
        );
    }
}
