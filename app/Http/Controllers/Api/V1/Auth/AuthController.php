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
use Illuminate\Support\Facades\Auth;

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
        $user = $this->authService->register($request->validated());

        Auth::login($user);

        $request->session()->regenerate();

        return ApiResponse::success(
            data: UserResource::make($user),
            message: 'Register berhasil',
            status: 201,
        );
    }

    /**
     * Login dan membuat token baru.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->login($request->validated());

        Auth::login($user);

        $request->session()->regenerate();

        return ApiResponse::success(
            data: UserResource::make($user),
            message: 'Login berhasil',
        );
    }

    /**
     * Logout dari perangkat yang sedang digunakan.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return ApiResponse::success(
            data: null,
            message: 'Logout berhasil',
        );
    }
}
