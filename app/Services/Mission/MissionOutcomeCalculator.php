<?php

namespace App\Services\Mission;

use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use Illuminate\Database\Eloquent\Collection;

class MissionOutcomeCalculator
{
    public function calculate(Collection $heroes, Quest $quest): MissionStatus
    {
        $partyLevel = $this->calculatePartyLevel($heroes);

        return $partyLevel > $quest->difficulty
            ? MissionStatus::Success
            : MissionStatus::Failure;
    }

    private function calculatePartyLevel(Collection $heroes): int
    {
        $totalHeroLevel = $heroes->sum('level');

        $uniqueClassBonus = max(
            0,
            $heroes->unique(
                fn (Hero $hero) => $hero->hero_class->value
            )->count() - 1
        );

        return $totalHeroLevel + $uniqueClassBonus;
    }
}