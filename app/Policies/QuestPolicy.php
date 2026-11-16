<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestPolicy
{
    public function create(User $user): Response
    {
        return Response::deny();
    }
}