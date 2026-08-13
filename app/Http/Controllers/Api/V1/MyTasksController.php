<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MyTasksResource;
use App\Services\TaskService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MyTasksController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->paginateForUser(
            user: $request->user(),
            search: $request->string('search')->toString(),
            perPage: (int) $request->integer(
                'per_page',
                15,
            ),
        );

        return ApiResponse::success(
            data: MyTasksResource::collection($tasks),
            message: 'Daftar my tasks berhasil diambil.',
            meta: [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        );
    }
}
