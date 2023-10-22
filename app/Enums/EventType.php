<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum EventType: string implements HasLabel, HasColor
{
    case COMPETITION = 'competition';
    case CLUB_LIFE = 'club_life';
    case VOLUNTEERS = 'volunteers';
    case CAMP = 'camp';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COMPETITION => 'Compétition',
            self::CLUB_LIFE => 'Vie du club',
            self::VOLUNTEERS => 'Bénévoles',
            self::CAMP => 'Camp',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COMPETITION => 'gray',
            self::CLUB_LIFE => 'success',
            self::VOLUNTEERS => 'warning',
            self::CAMP => 'primary',
        };
    }

    public function code(): ?string
    {
        return match ($this) {
            self::COMPETITION => '🏅',
            self::CLUB_LIFE => '🎽',
            self::VOLUNTEERS => '🤝',
            self::CAMP => '⛺️',
        };
    }
}
