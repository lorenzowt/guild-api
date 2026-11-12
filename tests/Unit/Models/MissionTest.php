<?php

use App\Enums\MissionStatus;
use App\Models\Mission;
use Tests\TestCase;

uses(TestCase::class);
test('new mission is assigned', function () {
    $mission = new Mission();

    expect($mission->isAssigned())->toBeTrue();
});

test('mission is not assigned after it succeeds', function () {
    $mission = new Mission();

    $mission->complete(MissionStatus::Success);

    expect($mission->isAssigned())->toBeFalse();
});

test('mission can be completed successfully', function () {
    $mission = new Mission();

    $mission->complete(MissionStatus::Success);

    expect($mission->status)->toBe(MissionStatus::Success)
        ->and($mission->completed_at)->not->toBeNull();
});

test('mission can be completed unsuccessfully', function () {
    $mission = new Mission();

    $mission->complete(MissionStatus::Failure);

    expect($mission->status)->toBe(MissionStatus::Failure)
        ->and($mission->completed_at)->not->toBeNull();
});

test('mission can be canceled', function () {
    $mission = new Mission();

    $mission->cancel();

    expect($mission->status)->toBe(MissionStatus::Canceled)
        ->and($mission->completed_at)->toBeNull()
        ->and($mission->isAssigned())->toBeFalse();
});