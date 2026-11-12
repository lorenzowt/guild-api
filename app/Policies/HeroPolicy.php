<?php

namespace App\Policies;

use App\Enums\HeroStatus;
use App\Models\Hero;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HeroPolicy
{
    public function view(User $user, Hero $hero): Response
    {
        if (
            $hero->user_id === $user->id
            && $hero->status !== HeroStatus::Deactivated
        ) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function update(User $user, Hero $hero): Response
    {
        if (
            $hero->user_id === $user->id
            && $hero->status !== HeroStatus::Deactivated
        ) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function delete(User $user, Hero $hero): Response
    {
        if (
            $hero->user_id === $user->id
            && $hero->status !== HeroStatus::Deactivated
        ) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    public function heal(User $user, Hero $hero): Response
    {
        if (
            $hero->user_id === $user->id
            && $hero->status !== HeroStatus::Deactivated
        ) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}