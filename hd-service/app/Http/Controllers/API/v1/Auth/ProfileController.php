<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\Auth\ChangePasswordRequest;
use App\Http\Requests\API\v1\Auth\UpdateProfileRequest;
use App\Http\Resources\v1\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function show()
    {
        return new UserResource(Auth::user());
    }

    /**
     * Update the authenticated user.
     */
    public function update(UpdateProfileRequest $request)
    {
        Auth::user()->update($request->validated());

        return new UserResource(Auth::user());
    }

    /**
     * Update the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        if (!Hash::check($data['password'], $user->password)) {
            return $this->sendMessageResponse(__('auth.password'), 400);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return new UserResource(auth()->user());
    }
}
