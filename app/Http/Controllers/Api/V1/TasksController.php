<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTasksRequest;
use App\Http\Requests\Tasks\UpdateTasksRequest;
use App\Http\Resources\V1\Tasks\TasksDetailResource;
use App\Http\Resources\V1\Tasks\TasksResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\TaskService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
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
            'viewAny',
            [Task::class, $project],
        );

        $tasks = $this->taskService->paginate(
            project: $project,
            search: $request->string('search')->toString(),
            perPage: (int) $request->integer('per_page', 15),
        );

        return ApiResponse::success(
            data: TasksResource::collection($tasks),
            message: 'Daftar task berhasil diambil.',
            meta: [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreTasksRequest $request,
        Workspace $workspace,
        Project $project,
    ): JsonResponse {
        $task = $this->taskService->create(
            project: $project,
            creator: $request->user(),
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: TasksResource::make(
                $task->load([
                    'project',
                    'kanbanColumn',
                    'creator',
                    'assignee',
                ])
            ),
            message: 'Task created successfully.',
            status: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Workspace $workspace,
        Project $project,
        Task $task,
    ): JsonResponse {
        Gate::authorize('view', $task);

        abort_unless(
            $task->project_id === $project->id,
            404,
        );

        return ApiResponse::success(
            data: TasksDetailResource::make(
                $task->load([
                    'project',
                    'kanbanColumn',
                    'creator',
                    'assignee',
                ])
            ),
            message: 'Detail task berhasil diambil.',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateTasksRequest $request,
        Project $project,
        Task $task,
    ): JsonResponse {
        abort_unless(
            $task->project_id === $project->id,
            404,
        );

        $task = $this->taskService->update(
            task: $task,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: TasksResource::make(
                $task->load([
                    'project',
                    'kanbanColumn',
                    'creator',
                    'assignee',
                ])
            ),
            message: 'Task berhasil diperbarui.',
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Project $project,
        Task $task,
    ): JsonResponse {
        Gate::authorize('delete', $task);

        abort_unless(
            $task->project_id === $project->id,
            404,
        );

        $this->taskService->delete($task);

        return ApiResponse::success(
            message: 'Task berhasil dihapus.',
        );
    }
}
