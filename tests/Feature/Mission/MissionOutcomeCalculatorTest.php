<?php

use App\Enums\HeroClass;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Services\Mission\MissionOutcomeCalculator;
use Illuminate\Database\Eloquent\Collection;

test('party level includes a bonus for each additional unique hero class', function () {
    $quest = new Quest();
    $quest->difficulty = 4;

    $warrior = new Hero();
    $warrior->hero_class = HeroClass::Warrior;
    $warrior->level = 2;

    $mage = new Hero();
    $mage->hero_class = HeroClass::Mage;
    $mage->level = 2;

    $heroes = new Collection([
        $warrior,
        $mage,
    ]);

    $outcome = (new MissionOutcomeCalculator())
        ->calculate($heroes, $quest);

    expect($outcome)->toBe(MissionStatus::Success);
});

test('mission fails when party level equals quest difficulty', function () {
    $quest = new Quest();
    $quest->difficulty = 3;

    $firstWarrior = new Hero();
    $firstWarrior->hero_class = HeroClass::Warrior;
    $firstWarrior->level = 2;

    $secondWarrior = new Hero();
    $secondWarrior->hero_class = HeroClass::Warrior;
    $secondWarrior->level = 1;

    $heroes = new Collection([
        $firstWarrior,
        $secondWarrior,
    ]);

    $outcome = (new MissionOutcomeCalculator())
        ->calculate($heroes, $quest);

    expect($outcome)->toBe(MissionStatus::Failure);
});