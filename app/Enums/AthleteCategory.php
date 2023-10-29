<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum AthleteCategory: string implements HasLabel, HasColor
{
    // case U8 = 'u8';
    case U10 = 'u10';
    case U12 = 'u12';
    case U14 = 'u14';
    case U16 = 'u16';
    case U18 = 'u18';
    case U20 = 'u20';
    case U23 = 'u23';
    case SENIOR = 'senior';

    public function getLabel(): ?string
    {
        return match ($this) {
            // self::U8 => 'U8',
            self::U10 => 'U10',
            self::U12 => 'U12',
            self::U14 => 'U14',
            self::U16 => 'U16',
            self::U18 => 'U18',
            self::U20 => 'U20',
            self::U23 => 'U23',
            self::SENIOR => 'Actif',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            // self::U8 => 'gray',
            self::U12 => 'warning',
            self::U14 => 'warning',
            self::U16 => 'info',
            self::U18 => 'success',
            self::U20 => 'success',
            self::U23 => 'success',
            self::SENIOR => 'success',
        };
    }

    public function group(): string|array|null|AthleteCategoryGroup
    {
        return match ($this) {
            // self::U8 => AthleteCategoryGroup::U14M,
            self::U12 => AthleteCategoryGroup::U14M,
            self::U14 => AthleteCategoryGroup::U14M,
            self::U16 => AthleteCategoryGroup::U14M,
            self::U18 => AthleteCategoryGroup::U16P,
            self::U20 => AthleteCategoryGroup::U16P,
            self::U23 => AthleteCategoryGroup::U16P,
            self::SENIOR => AthleteCategoryGroup::U16P,
        };
    }
}
