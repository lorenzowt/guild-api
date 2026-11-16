<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MissionPolicy
{
    public function view(User $user, Mission $mission): Response
    {
        if ($mission->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
