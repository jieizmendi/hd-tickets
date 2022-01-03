<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoginAttempt;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Attemp authentication.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        LoginAttempt::dispatch($credentials);

        if (!Auth::attempt(Arr::only($credentials, ['email', 'password']))) {
            return $this->sendMessageResponse(__('auth.failed'), 401);
        }

        $token = $request->user()->createToken($credentials['device'])->plainTextToken;

        LoggedIn::dispatch($token);

        return response()->json([
            'access_token' => $token,
            'type' => 'Bearer',
        ]);
    }
}
