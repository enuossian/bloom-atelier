<?php

namespace App\Enum;

enum SessionStatus: string
{
    case Available = 'available';
    case Full = 'full';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            SessionStatus::Available => 'Disponible',
            SessionStatus::Full => 'Complète',
            SessionStatus::Completed => 'Terminée',
        };
    }

    // badge couleur pour Bootstrap 5
    public function badge(): string
    {
        return match ($this) {
            SessionStatus::Available => 'success',
            SessionStatus::Full => 'warning',
            SessionStatus::Completed => 'dark',
        };
    }
}
