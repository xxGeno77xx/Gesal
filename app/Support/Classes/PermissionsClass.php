<?php

namespace App\Support\Classes;

use Spatie\Enum\Enum;

/** //=================================================

 * //==================================================
 * EVENEMENT
 * @method static self event_create()
 * @method static self event_read()
 * @method static self event_update()
 * @method static self event_delete()
 * //==================================================
 *  SALLES
 * @method static self salles_manage()
 *=====================================================
 * AUTHENTICATION
 * @method static self Management()
 * ====================================================
 * 
 */

class PermissionsClass extends Enum
{

    protected static function values()
    {
        return function(string $name): string|int {

            $traductions = array(
                "event" => "Evènement",
                "create" => "ajouter",
                "read" => "voir",
                "update" => "modifier",
                "delete" => "supprimer",
                "users"=>"Utilisateurs"
            );
            return strtr(str_replace("_", ": ", str($name)), $traductions);;
        };
    }
}