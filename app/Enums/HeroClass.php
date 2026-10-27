<?php

namespace App\Enums;

enum HeroClass: string
{
    case Cleric = 'cleric';
    case Mage = 'mage';
    case Rogue = 'rogue';
    case Warrior = 'warrior';

    public function label()
    {
        return match($this){
            self::Cleric => 'Cleric',
            self::Mage => 'Mage',
            self::Rogue => 'Rogue',
            self::Warrior => 'Warrior',
        };
    }
}