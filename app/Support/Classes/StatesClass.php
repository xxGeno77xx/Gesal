<?php

namespace App\Support\Classes;

use Spatie\Enum\Enum;

/**
 * @method static self Activated()
 * @method static self Deactivated()
 * @method static self Suspended()
 * =======================================
 * @method static self Validated()
 * @method static self Rejected()
 * @method static self Pending()
 * @method static self ValidateByDirector()
 * 
 */
class StatesClass extends Enum
{

    protected static function values(): array
    {
        return [
            'Activated' => 'Activé(e)',
            'Deactivated' => 'Désactivé(e)',
            'Suspended' => 'Suspendu(e)',

            'Validated'=> 'Validé(e)',
            'Rejected'=> 'Rejeté(e)',
            'Pending'=> 'En attente de validation',
            'ValidateByDirector'=> 'Prioritaire',
        ];
    }
}