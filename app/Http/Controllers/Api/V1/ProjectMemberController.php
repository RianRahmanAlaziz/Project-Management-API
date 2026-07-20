<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectMember\InviteProjectMemberRequest;
use App\Http\Requests\ProjectMember\UpdateProjectMemberRequest;
use App\Http\Resources\V1\ProjectMemberResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Workspace;
use App\Services\ProjectMemberService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController extends Controller
{
    public function __construct(
        private readonly ProjectMemberService $projectMemberService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(
        Request $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        Gate::authorize('view', $project);

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100,
        );

        $search = trim(
            (string) $request->query('search', '')
        );

        $members = $this->projectMemberService
            ->paginateForProject(
                project: $project,
                perPage: $perPage,
                search: $search,
            );

        return ApiResponse::success(
            data: ProjectMemberResource::collection(
                $members->getCollection()
            )->resolve($request),

            message: 'Project members retrieved successfully.',

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


    /**
     * Store a newly created resource in storage.
     */
    public function store(
        InviteProjectMemberRequest $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        $membership = $this->projectMemberService->create(
            project: $project,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new ProjectMemberResource($membership),
            message: 'Project member added successfully.',
            status: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateProjectMemberRequest $request,
        Workspace $workspace,
        Project $project,
        ProjectMember $member,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        $membership = $this->projectMemberService->update(
            project: $project,
            membership: $member,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new ProjectMemberResource($membership),
            message: 'Project member updated successfully.',
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Workspace $workspace,
        Project $project,
        ProjectMember $member,
    ): JsonResponse {
        $this->ensureProjectBelongsToWorkspace(
            $workspace,
            $project,
        );

        Gate::authorize(
            'manageMembers',
            $project,
        );

        $this->projectMemberService->delete(
            project: $project,
            membership: $member,
        );

        return ApiResponse::success(
            data: null,
            message: 'Project member removed successfully.',
        );
    }

    /**
     * Ensure project belongs to requested workspace.
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
