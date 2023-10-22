<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum AthleteCategoryGroup: string implements HasLabel, HasColor
{
    case U16P = 'u16p';
    case U14M = 'u14m';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::U16P => 'U16+',
            self::U14M => 'U14-',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::U16P => 'info',
            self::U14M => 'warning',
        };
    }
}
