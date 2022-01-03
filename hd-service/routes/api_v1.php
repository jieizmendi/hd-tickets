<?php

use App\Http\Controllers\API\v1\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\API\v1\Auth\LogoutController as AuthLogoutController;
use App\Http\Controllers\API\v1\Auth\ProfileController as AuthProfileController;
use App\Http\Controllers\API\v1\Auth\RegisterController as AuthRegisterController;
use App\Http\Controllers\API\v1\MetricsController;
use App\Http\Controllers\API\v1\PriorityController;
use App\Http\Controllers\API\v1\TagController;
use App\Http\Controllers\API\v1\TicketController;
use App\Http\Controllers\API\v1\Ticket\AgentController as TicketAgentController;
use App\Http\Controllers\API\v1\Ticket\FileController as TicketFileController;
use App\Http\Controllers\API\v1\Ticket\FlagController as TicketFlagController;
use App\Http\Controllers\API\v1\Ticket\TagController as TicketTagController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\User\TagController as UserTagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::name('auth.')->prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/login', AuthLoginController::class)->name('login');
        Route::post('/register', AuthRegisterController::class)->name('register');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', AuthLogoutController::class)->name('logout');

        Route::name('profile.')->prefix('profile')->group(function () {
            Route::get('', [AuthProfileController::class, 'show'])->name('show');
            Route::put('', [AuthProfileController::class, 'update'])->name('update');
            Route::post('/password', [AuthProfileController::class, 'changePassword'])->name('change-password');
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('priorities', PriorityController::class);
    Route::apiResource('tags', TagController::class);

    Route::post('users/{user}/tags/{tag}', [UserTagController::class, 'add'])->name('users.tags.add');
    Route::delete('users/{user}/tags/{tag}', [UserTagController::class, 'remove'])->name('users.tags.remove');
    Route::apiResource('users', UserController::class)->except('store');

    Route::name('tickets.')->prefix('tickets/{ticket}')->group(function () {
        Route::post('/files', [TicketFileController::class, 'add'])->name('files.add');
        Route::delete('/files/{file}', [TicketFileController::class, 'remove'])->name('files.remove');

        Route::post('/flags/{flag}', [TicketFlagController::class, 'create'])->name('flags.create');
        Route::delete('/flags/{flag}', [TicketFlagController::class, 'remove'])->name('flags.remove');

        Route::post('/tags/{tag}', [TicketTagController::class, 'add'])->name('tags.add');
        Route::delete('/tags/{tag}', [TicketTagController::class, 'remove'])->name('tags.remove');

        Route::post('/agents/{user}', [TicketAgentController::class, 'attach'])->name('agents.attach');
        Route::delete('/agents/{user}', [TicketAgentController::class, 'detach'])->name('agents.detach');

        Route::post('/transition', [TicketController::class, 'transition'])->name('transition');
    });

    Route::apiResource('tickets', TicketController::class)->except('destroy');

    Route::get('metrics', MetricsController::class)->name('metrics');
});
