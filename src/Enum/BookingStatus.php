<?php

namespace App\Enum;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            // self::Pending
            BookingStatus::Pending => 'En attente',
            BookingStatus::Paid => 'Payée',
            BookingStatus::Cancelled => 'Annulée',
        };
    }
}
