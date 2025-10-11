<?php

namespace App\Enums;

enum ServiceEnum: string
{
    case MAKAN_AJA = 'MAKAN_AJA';
    case BELANJA_AJA = 'BELANJA_AJA';
    case KIRIM_AJA = 'KIRIM_AJA';
    case MARKET_AJA = 'MARKET_AJA';

    public function label(): string
    {
        return match ($this) {
            static::MAKAN_AJA => 'Makan Aja',
            static::BELANJA_AJA => 'Belanja Aja',
            static::KIRIM_AJA => 'Kirim Aja',
            static::MARKET_AJA => 'Market Aja',
        };
    }
}
