<?php

namespace App\Exceptions\Ticket;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidTransitionException extends Exception
{
    /**
     * Initial status.
     *
     * @var string
     */
    protected $from;

    /**
     * Next status.
     *
     * @var string
     */
    protected $to;

    /**
     * Create the instance.
     */
    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
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
            'message' => __('statuses.invalid-transition', [
                'from' => $this->from,
                'to' => $this->to,
            ]),
        ], 400);
    }
}
