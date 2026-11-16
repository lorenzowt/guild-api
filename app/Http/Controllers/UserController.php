<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateProfileRequest;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return (new UserResource($request->user()))
            ->additional([
                'message' => 'User profile retrieved successfully.',
            ]);
    }

    public function updateMe(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return (new UserResource($user->fresh()))
            ->additional([
                'message' => 'User profile updated successfully.',
            ]);
    }
}