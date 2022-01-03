<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserIsNotAgentException extends Exception
{
    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Create the instance.
     */
    public function __construct(string $user)
    {
        $this->user = $user;
    }

    /**
     * Report the exception.
     */
    public function report(): bool
    {
        return false;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => __('users.not-agent'),
        ], 400);
    }
}
