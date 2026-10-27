<?php

namespace App\Enums;

enum HeroStatus: string
{
    case Available = 'available';
    case OnMission = 'on_mission';
    case Injured = 'injured';

    public function label()
    {
        return match($this){
            self::Available => 'Available',
            self::OnMission => 'On Mission',
            self::Injured => 'Injured',
        };
    }
}