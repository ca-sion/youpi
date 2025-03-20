<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasColor, HasLabel
{
    case INFORMATION = 'information';
    case LETTER = 'letter';
    case TRAVEL = 'travel';
    case NOTICE = 'notice';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INFORMATION => 'Information',
            self::LETTER      => 'Lettre',
            self::TRAVEL      => 'Déplacement',
            self::NOTICE      => 'Notice',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INFORMATION => 'gray',
            self::LETTER      => 'gray',
            self::TRAVEL      => 'warning',
            self::NOTICE      => 'primary',
        };
    }
}
