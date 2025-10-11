<?php

namespace SayaEat\Shared\Enums\MarketAja;

enum DiscountType: string
{
    case FIXED = 'FIXED';
    case PERCENTAGE = 'PERCENTAGE';

    public static function toSelectOptions(): array
    {
        return collect(self::cases())->reduce(function ($prev, $item) {
            $prev[$item->name] = $item->value;
            return $prev;
        }, []);
    }
}
