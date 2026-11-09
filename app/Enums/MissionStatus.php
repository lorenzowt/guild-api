<?php

namespace App\Enums;

enum MissionStatus: string
{
    case Assigned = 'assigned';
    case Success = 'success';
    case Failure = 'failure';
    case Canceled = 'canceled';

    public function label()
    {
        return match ($this) {
            self::Assigned => 'Assigned',
            self::Success => 'Success',
            self::Failure => 'Failure',
            self::Canceled => 'Canceled',
        };
    }
}
