<?php

namespace App\Services\Mission;

use App\Enums\HeroStatus;
use App\Models\Mission;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMissionService
{
    public function execute(User $user, array $data): Mission
    {
        return DB::transaction(function () use ($user, $data) {

            $quest = Quest::active()
                ->findOrFail($data['quest_id']);

            $heroIds = $data['hero_ids'];
            sort($heroIds);

            $heroes = $user->heroes()
                ->whereIn('id', $heroIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($heroes->count() !== count($heroIds)) {
                throw ValidationException::withMessages([
                    'hero_ids' => 'One or more selected heroes are invalid.',
                ]);
            }

            if ($heroes->contains(
                fn ($hero) => $hero->status !== HeroStatus::Available
            )) {
                throw ValidationException::withMessages([
                    'hero_ids' => 'All selected heroes must be in available status.',
                ]);
            }

            $mission = $user->missions()->create([
                'quest_id' => $quest->id,
            ]);

            $mission->heroes()->attach($heroIds);

            $user->heroes()
                ->whereIn('id', $heroIds)
                ->update([
                    'status' => HeroStatus::OnMission->value,
                ]);

            return $mission;
        });
    }
}
