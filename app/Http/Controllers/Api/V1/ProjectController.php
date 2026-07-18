<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use App\Services\ProjectService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(
        Request $request,
        Workspace $workspace,
    ): JsonResponse {
        Gate::authorize('view', $workspace);

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100,
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $projects = $this->projectService
            ->paginateForProject(
                workspace: $workspace,
                perPage: $perPage,
                search: $search,
            );

        return ApiResponse::success(
            data: ProjectResource::collection(
                $projects->getCollection()
            )->resolve($request),
            message: 'Project berhasil diambil',
            meta: [
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ],
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreProjectRequest $request,
        Workspace $workspace,
    ): JsonResponse {
        $project = $this->projectService->create(
            $workspace,
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::success(
            data: new ProjectResource($project),
            message: 'Project created successfully.',
            status: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        Gate::authorize('view', $project);

        return ApiResponse::success(
            data: new ProjectResource(
                $project->load([
                    'owner',
                    'members',
                ]),
            ),
            message: 'Detail Project berhasil diambil',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateProjectRequest $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        $project = $this->projectService->update(
            $project,
            $request->validated(),
        );

        return ApiResponse::success(
            data: new ProjectResource($project),
            message: 'Project updated successfully.',
        );
    }

    public function destroy(
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        Gate::authorize('delete', $project);

        $this->projectService->delete($project);

        return ApiResponse::success(
            data: null,
            message: 'Project deleted successfully.',
        );
    }

    /**
     * Ensure nested project belongs to the workspace.
     */
    private function ensureProjectBelongsToWorkspace(
        Workspace $workspace,
        Project $project,
    ): void {
        abort_unless(
            $project->workspace_id === $workspace->id,
            404,
        );
    }
}
