<?php

namespace App\Enum;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            // self::Pending
            BookingStatus::Pending => 'En attente',
            BookingStatus::Paid => 'Payée',
        };
    }
}
