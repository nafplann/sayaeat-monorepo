<?php

namespace SayaEat\Shared\Enums;

enum TrueFalseEnum: int
{
    case NO = 0;
    case YES = 1;
    
    public static function toSelectOptions(): array
    {
        return collect(self::cases())->reduce(function ($prev, $item) {
            $prev[$item->name] = $item->value;
            return $prev;
        }, []);
    }
}
