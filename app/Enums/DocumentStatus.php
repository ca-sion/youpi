<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum DocumentStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case VALIDATED = 'validated';
    case EXPIRED = 'expired';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::VALIDATED => 'Validé',
            self::EXPIRED => 'Expiré',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::VALIDATED => 'success',
            self::EXPIRED => 'danger',
        };
    }

    public function getBackgroundColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'darkorange',
            self::VALIDATED => 'steelblue',
            self::EXPIRED => 'crimson',
        };
    }
}
