<?php

use App\Enums\HeroStatus;
use App\Models\Hero;

test('hero levels up by one', function () {
    $hero = Hero::factory()->make([
        'level' => 4,
    ]);

    $hero->levelUp();

    expect($hero->level)->toBe(5);
});

test('hero level cannot exceed ten', function () {
    $hero = Hero::factory()->make([
        'level' => 10,
    ]);

    $hero->levelUp();

    expect($hero->level)->toBe(10);
});

test('hero levels down by one', function () {
    $hero = Hero::factory()->make([
        'level' => 4,
    ]);

    $hero->levelDown();

    expect($hero->level)->toBe(3);
});

test('hero level cannot go below one', function () {
    $hero = Hero::factory()->make([
        'level' => 1,
    ]);

    $hero->levelDown();

    expect($hero->level)->toBe(1);
});

test('hero can become available', function () {
    $hero = Hero::factory()->make([
        'status' => HeroStatus::OnMission,
    ]);

    $hero->makeAvailable();

    expect($hero->status)->toBe(HeroStatus::Available);
});

test('hero can become injured', function () {
    $hero = Hero::factory()->make([
        'status' => HeroStatus::OnMission,
    ]);

    $hero->injure();

    expect($hero->status)->toBe(HeroStatus::Injured);
});