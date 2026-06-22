<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    /**
     * Menampilkan user yang sedang login.
     */
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            data: UserResource::make($request->user())
                ->resolve($request),
            message: 'Profile berhasil diambil',
        );
    }

    /**
     * Memperbarui profile user.
     */
    public function update(
        UpdateProfileRequest $request
    ): JsonResponse {
        $user = $request->user();

        $user->update($request->validated());

        return ApiResponse::success(
            data: UserResource::make($user->refresh())
                ->resolve($request),
            message: 'Profile berhasil diperbarui',
        );
    }
}
