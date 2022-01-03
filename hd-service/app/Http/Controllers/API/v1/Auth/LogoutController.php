<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Events\Auth\LoggedOut;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use \Illuminate\Http\JsonResponse;

class LogoutController extends Controller
{
    /**
     * Attemp logout.
     */
    public function __invoke(): JsonResponse
    {
        $tokenId = Str::before(request()->bearerToken(), '|');
        Auth::user()->tokens()->where('id', $tokenId)->delete();

        LoggedOut::dispatch(request()->bearerToken());

        return response()->json(null, 204);
    }

}
