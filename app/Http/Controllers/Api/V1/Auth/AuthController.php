<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Mendaftarkan user baru.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->validated()
        );

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])
                    ->resolve($request),

                'access_token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Register berhasil',
            status: 201,
        );
    }

    /**
     * Login dan membuat token baru.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated()
        );

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])
                    ->resolve($request),

                'access_token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Login berhasil',
        );
    }

    /**
     * Logout dari perangkat yang sedang digunakan.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(
            data: null,
            message: 'Logout berhasil',
        );
    }
}
