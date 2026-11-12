<?php

namespace App\Services\Mission;

use App\Enums\MissionStatus;
use App\Models\Mission;
use Illuminate\Support\Facades\DB;
use App\Services\Mission\MissionOutcomeCalculator;

class CompleteMissionService
{
    public function __construct(
        private MissionOutcomeCalculator $calculator
    ) {
    }

    public function complete(Mission $mission): ?Mission
    {
        return DB::transaction(function () use ($mission) {
            $mission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            if (! $mission->isAssigned()) {
                return null;
            }

            $mission->load([
                'quest',
                'heroes' => fn ($query) => $query->lockForUpdate(),
            ]);

            $outcome = $this->calculator->calculate(
                $mission->heroes,
                $mission->quest
            );

            foreach ($mission->heroes as $hero) {
                if ($outcome === MissionStatus::Success) {
                    $hero->levelUp();
                    $hero->makeAvailable();
                } else {
                    $hero->levelDown();
                    $hero->injure();
                }

                $hero->save();
            }

            $mission->complete($outcome);
            $mission->save();

            return $mission;
        });
    }
}