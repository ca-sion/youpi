<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum EventStatus: string implements HasLabel, HasColor
{
    case PLANNED = 'planned';
    case PROVISIONAL = 'provisional';
    case REPORTED = 'reported';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PLANNED => 'Prévu',
            self::PROVISIONAL => 'Provisoire',
            self::REPORTED => 'Reporté',
            self::CANCELLED => 'Annulé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PLANNED => 'success',
            self::PROVISIONAL => 'gray',
            self::REPORTED => 'warning',
            self::CANCELLED => 'danger',
        };
    }
}
