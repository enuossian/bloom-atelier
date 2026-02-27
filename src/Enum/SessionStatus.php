<?php

namespace App\Enum;

enum SessionStatus: string
{
    case Available = 'available';
    case Full = 'full';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            SessionStatus::Available => 'Disponible',
            SessionStatus::Full => 'Complète',
            SessionStatus::Cancelled => 'Annulée',
        };
    }
}
