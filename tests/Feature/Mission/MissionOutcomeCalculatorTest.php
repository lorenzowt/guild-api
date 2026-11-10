<?php

use App\Enums\HeroClass;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Services\Missions\MissionOutcomeCalculator;
use Illuminate\Database\Eloquent\Collection;

test('party level includes a bonus for each additional unique hero class', function () {
    $quest = Quest::factory()->make([
        'difficulty' => 5,
    ]);

    $heroes = new Collection([
        Hero::factory()->make([
            'hero_class' => HeroClass::Warrior,
            'level' => 2,
        ]),
        Hero::factory()->make([
            'hero_class' => HeroClass::Mage,
            'level' => 2,
        ]),
    ]);

    $outcome = app(MissionOutcomeCalculator::class)
        ->calculate($heroes, $quest);

    expect($outcome)->toBe(MissionStatus::Success);
});

test('mission fails when party level equals quest difficulty', function () {
    $quest = Quest::factory()->make([
        'difficulty' => 3,
    ]);

    $heroes = new Collection([
        Hero::factory()->make([
            'hero_class' => HeroClass::Warrior,
            'level' => 2,
        ]),
        Hero::factory()->make([
            'hero_class' => HeroClass::Warrior,
            'level' => 1,
        ]),
    ]);

    $outcome = app(MissionOutcomeCalculator::class)
        ->calculate($heroes, $quest);

    expect($outcome)->toBe(MissionStatus::Failure);
});