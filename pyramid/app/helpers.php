<?php

if (!function_exists('get_user_coordinate')) {
    function get_user_coordinate()
    {
        $user = request()->user();

        if (!$user) {
            // Default coordinate (Manokwari)
            $manokwari = (object)config('branch.MNK');
            return [$manokwari->latitude, $manokwari->longitude];
        }

        $defaultAddress = $user->addresses()->where('default', true)->first();
        return [$defaultAddress->latitude, $defaultAddress->longitude];
    }
}

if (!function_exists('get_user_default_address')) {
    function get_user_default_address()
    {
        $user = request()->user();
        return $user->addresses()->where('default', true)->first();
    }
}

if (!function_exists('display_price')) {
    function display_price(float|int $price): string
    {
        return number_format($price, 0, ',', '.');
    }
}

if (!function_exists('roundup_to_one_thousand')) {
    function roundup_to_one_thousand(float|int $amount): int
    {
        $originalAmount = $amount;

        $indonesiaBankNotes = array(
            100_000 => 0,
            75_000 => 0,
            50_000 => 0,
            20_000 => 0,
            10_000 => 0,
            5_000 => 0,
            2_000 => 0,
            1_000 => 0
        );

        foreach (array_keys($indonesiaBankNotes) as $note) {
            if ($amount >= $note) {
                $indonesiaBankNotes[$note] = intval($amount / $note);
                $amount = $amount - $indonesiaBankNotes[$note] * $note;
            }
        }

        $total = 0;

        foreach ($indonesiaBankNotes as $note => $count) {
            $total += $note * $count;
        }

        $amountDiff = $originalAmount - $total;
        $finalAmount = $total;

        if ($amountDiff > 0 && $amountDiff < 1000) {
            $finalAmount += 1000;
        }

        return $finalAmount;
    }
}
