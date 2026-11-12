<?php

namespace App\Services\Mission;

use App\Models\Mission;
use Illuminate\Support\Facades\DB;

class CancelMissionService
{
    public function cancel(Mission $mission): ?Mission
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

            foreach ($mission->heroes as $hero) {
                $hero->makeAvailable();
                $hero->save();
            }

            $mission->cancel();
            $mission->save();

            return $mission;
        });
    }
}