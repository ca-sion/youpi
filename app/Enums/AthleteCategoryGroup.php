<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AthleteCategoryGroup: string implements HasColor, HasLabel
{
    case U16P = 'u16p';
    case U14M = 'u14m';
    case U18P_MID_DIST = 'u18p_mid_dist';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::U16P          => 'U16+',
            self::U14M          => 'U14-',
            self::U18P_MID_DIST => 'Demi-fond U18+',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::U16P          => 'info',
            self::U14M          => 'warning',
            self::U18P_MID_DIST => 'danger',
        };
    }
}
