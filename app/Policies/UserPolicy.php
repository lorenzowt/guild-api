<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): Response
    {
        return Response::deny();
    }

    public function view(User $user, User $targetUser): Response
    {
        return Response::deny();
    }

        public function update(User $user, User $targetUser): Response
    {
        return Response::deny();
    }
}