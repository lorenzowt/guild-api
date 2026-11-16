<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexResourceRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\User\UpdateUserRequest;

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

    public function index(IndexResourceRequest $request)
    {
        Gate::authorize('viewAny', User::class);

        $perPage = $request->validated()['per_page'] ?? 10;

        $users = User::query()
            ->paginate($perPage);

        return UserResource::collection($users)
            ->additional([
                'message' => 'Users retrieved successfully.',
            ]);
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        return (new UserResource($user))
            ->additional([
                'message' => 'User retrieved successfully.',
            ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);

        $user->update($request->validated());

        return (new UserResource($user->fresh()))
            ->additional([
                'message' => 'User updated successfully.',
            ]);
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }


}