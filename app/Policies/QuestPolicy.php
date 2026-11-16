<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quest;
use Illuminate\Auth\Access\Response;

class QuestPolicy
{
    public function create(User $user): Response
    {
        return Response::deny();
    }

    public function update(User $user, Quest $quest): Response
    {
        return Response::deny();
    }
}