<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $perPage = min(
            max($request->integer('per_page', 15), 1),
            100,
        );

        $users = $this->userService->paginate(
            search: $request->string('search')
                ->trim()
                ->value() ?: null,
            perPage: $perPage,
        );

        return ApiResponse::success(
            data: UserResource::collection(
                $users->items(),
            )->resolve($request),
            message: 'Daftar user berhasil diambil',
            meta: [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        );
    }

    public function store(
        StoreUserRequest $request,
    ): JsonResponse {
        $user = $this->userService->create(
            $request->validated(),
        );

        return ApiResponse::success(
            data: UserResource::make($user)
                ->resolve($request),
            message: 'User berhasil dibuat',
            status: 201,
        );
    }

    public function show(
        Request $request,
        User $user,
    ): JsonResponse {
        Gate::authorize('view', $user);

        return ApiResponse::success(
            data: UserResource::make($user)
                ->resolve($request),
            message: 'Detail user berhasil diambil',
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
    ): JsonResponse {
        $user = $this->userService->update(
            user: $user,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: UserResource::make($user)
                ->resolve($request),
            message: 'User berhasil diperbarui',
        );
    }

    public function destroy(
        User $user,
    ): JsonResponse {
        Gate::authorize('delete', $user);

        $this->userService->delete($user);

        return ApiResponse::success(
            data: null,
            message: 'User berhasil dihapus',
        );
    }
}
