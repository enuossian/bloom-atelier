<?php

namespace App\Enum;

enum SessionStatus: string
{
    case Available = 'available';
    case Full = 'full';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            SessionStatus::Available => 'Disponible',
            SessionStatus::Full => 'Complète',
            SessionStatus::Cancelled => 'Annulée',
            SessionStatus::Completed => 'Terminée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            SessionStatus::Available => 'success',
            SessionStatus::Full => 'warning',
            SessionStatus::Cancelled => 'danger',
            SessionStatus::Completed => 'secondary',
        };
    }
}
