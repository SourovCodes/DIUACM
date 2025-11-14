<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case SSLCOMMERZ = 'sslcommerz';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SSLCOMMERZ => 'SSLCommerz',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SSLCOMMERZ => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SSLCOMMERZ => 'heroicon-m-credit-card',
        };
    }
}
